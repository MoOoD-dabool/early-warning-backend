# نظام الإنذار المبكر للكوارث في سوريا
# Early Warning System for Disasters in Syria

[🇸🇾 العربية](#-العربية) · [🇬🇧 English](#-english)

---

## 🇸🇾 العربية

نظام إنذار مبكر يرصد **الزلازل والسيول والفيضانات والعواصف** في سوريا، ويحدد **المحافظات المتأثرة**، ثم يرسل تنبيهاً فورياً إلى سكانها على هواتفهم، مع **صفارة إنذار** عند الخطر المرتفع وإرشادات سلامة بالعربية والإنجليزية.

هذا المستودع هو **الخادم (Backend)** ولوحة الإدارة. تطبيق الهاتف (Flutter) في مشروع منفصل.

### كيف يعمل؟
1. يستقبل الخادم الزلازل لحظة تسجيلها من **EMSC** (اتصال WebSocket دائم)، ويجلب الطقس وتدفق الأنهار كل ساعة من **Open-Meteo**.
2. يطبّق قواعد جغرافية وعلمية مبسطة ليحدد المحافظات المتأثرة وشدة الخطر في كل منها.
3. ينشئ تنبيهاً لكل محافظة ويرسله إلى مستخدميها عبر **Firebase Cloud Messaging**.

### أهم المزايا
- تنبيهات فورية تعمل حتى والتطبيق مغلق، مع صفارة إنذار عند الشدة العالية
- إرشادات السلامة قبل الكارثة وأثنائها وبعدها (عربي / إنجليزي)
- «هل شعرت بشيء؟»، والبلاغات، وطلبات الإغاثة
- تسجيل بالبريد مع رمز تحقق، أو بحساب Google
- لوحة إدارة (Filament) بدورين: مشرف ومشرف أعلى، مع تحقق ثنائي (2FA) وسجل نشاط

### التقنيات
PHP 8.2 · Laravel 12 · Sanctum · Filament · MySQL (SQLite للتطوير) · Firebase Cloud Messaging · Docker · Railway

### التشغيل محلياً
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan earthquake:listen   # نافذة منفصلة: استقبال الزلازل
php artisan schedule:work       # نافذة منفصلة: جلب الطقس كل ساعة
```
المتطلبات: PHP 8.2 مع إضافة `intl`. الإشعارات تحتاج ملف حساب خدمة Firebase (لا يُرفع إلى المستودع). راجع `.env.example`.

### النشر
الخادم يعمل على **Railway** داخل حاوية Docker. الشرح الكامل في [docs/DEPLOY_RAILWAY.md](docs/DEPLOY_RAILWAY.md).

### التفاصيل

<details>
<summary><b>ميزات التطبيق (Flutter)</b></summary>

- صفحة رئيسية تفاعلية: آخر زلزال، طقس المحافظات (محافظة المستخدم أولاً)، أرقام أحداث اليوم بتوقيت دمشق، ولافتة «التحذير الفعال»؛ والضغط على أي عنصر يفتح تفاصيله
- توقعات الطقس لسبعة أيام لأي محافظة
- قائمة تنبيهات مع تصفية حسب نوع الخطر، وسجل الزلازل السابقة
- إرشادات السلامة (قبل/أثناء/بعد) لكل نوع خطر
- «هل شعرت بشيء؟» بمقياس ميركالي (II–XII)، والبلاغات مع متابعة حالتها، وطلب الإغاثة وعرض فرق الإغاثة وحالتها ونوعها
- محاكي خطر تدريبي: صفارة وتنبيه وإرشادات لنوع كارثة يختاره المستخدم، دون إرسال أي بيانات
- العمل **بدون اتصال**: يحفظ الإرشادات والطقس والتنبيهات ويعرضها عند انقطاع الإنترنت
- عربي/إنجليزي، ووضع ليلي/نهاري، ومفتاحا الإشعارات والصوت (يُطبَّقان على الخادم)
</details>

<details>
<summary><b>قواعد إصدار التنبيه</b></summary>

**الزلازل:** يُقدَّر نصف قطر التأثير من القوة، ثم تُنبَّه كل محافظة داخله بشدة تقلّ بالبعد عن المركز (قانون هافرساين):

| القوة | نصف القطر |
|---|---|
| 2.0 – 3.0 | 30 كم |
| 3.0 – 4.0 | 60 كم |
| 4.0 – 5.0 | 120 كم |
| 5.0 – 6.0 | 220 كم |
| 6.0 – 7.0 | 350 كم |
| 7.0 فأكثر | 500 كم |

ويُقلَّل نصف القطر حتى النصف للزلازل الأعمق من 100 كم.

الصفارة تعمل عند قوة ≥ 3.0 **وشدة high/critical معاً**، أي عملياً من قوة ≈ 5.5 عند أقرب محافظة.

**التسونامي:** مركز في البحر المقابل للساحل + قوة ≥ 6.0 + عمق ≤ 100 كم ← تنبيه لطرطوس واللاذقية بصفارة.

**العواصف** (يلزم تحقق الشروط الثلاثة معاً): رياح + ضغط منخفض + مطر ≥ 1 mm/h.

| | داخلية `severe_storm` | ساحلية `coastal_storm` |
|---|---|---|
| الرياح | ≥ 60 كم/س (حرج ≥ 80) | ≥ 65 كم/س (حرج ≥ 90) |
| الضغط | ≤ 1000 hPa | ≤ 995 hPa |

اعتُمد «عاصفة» بدل «إعصار» لأن الأعاصير المدارية لا تتشكل قرب الساحل السوري.

**السيول:** رموز مطر غزير/عاصفة رعدية، في 9 محافظات ذات أودية فقط.

**الفيضانات:** سد الرستن (حمص، حماة) بمقارنة التدفق بسعة المفيض، والفرات (الحسكة، الرقة، دير الزور) بمقارنة التدفق بمتوسط 30 يوماً، وبقية الأنهار بهطول مرتفع مع تدفق فوق المعتاد.

**التهدئة:** لا يتكرر تنبيه الطقس نفسه لنفس المحافظة خلال 24 ساعة.

الأرقام كلها في `config/earthquake.php` و`config/weather_alerts.php`.
</details>

<details>
<summary><b>لوحة الإدارة</b></summary>

على المسار `/admin`، تسجيل الدخول ببريد وكلمة مرور ثم رمز تطبيق مصادقة (إلزامي).
- **مشرف:** إضافة وتعديل المحافظات وأنواع الأخطار، وعرض وتعديل التنبيهات والزلازل (لا تُنشأ يدوياً لأنها لا تمر بخدمة الإرسال)، وإدارة فرق الإغاثة وحالتها، والرد على البلاغات (يصل الرد بالبريد)، وحذف المستخدمين، وعرض طلبات الإغاثة وبلاغات الشعور بالهزة وسجلات الطقس والأجهزة ووصول التنبيهات.
- **مشرف أعلى:** كل ما سبق + **إرسال تنبيه يدوي** (لمحافظة أو لكل المحافظات، بشدة critical وصفارة، مع مربع تأكيد وبادئة توضح الفئة: محاكاة / كارثة لم يرصدها النظام / حدث وطني عام) + **محاكاة كارثة واقعية** (قيم فيزيائية تمر بشروط الإنذار الحقيقية نفسها) + إدارة المشرفين + سجل النشاط.
- المحافظات الـ16 وأنواع الأخطار الـ7 الأصلية محمية من الحذف؛ وما يُضاف لاحقاً يحذفه المشرف الأعلى فقط بعد كتابة اسمه للتأكيد.
</details>

<details>
<summary><b>الأمان</b></summary>

- كلمات المرور مجزّأة (bcrypt)، وسياسة 8–24 حرفاً إنجليزياً وأرقاماً
- رمز التحقق OTP مجزّأ ولا يُكتب في السجلات
- مفاتيح Sanctum تنتهي بعد 90 يوماً وتُلغى عند الخروج
- 5 محاولات/دقيقة للدخول والتسجيل والرمز واستعادة كلمة المرور (ثم 429)
- HTTPS وملفات تعريف ارتباط آمنة، ووضع التصحيح متوقف في الإنتاج
- الأسرار في متغيرات البيئة فقط ولا تُرفع إلى المستودع
</details>

<details>
<summary><b>البريد الإلكتروني</b></summary>

Railway يحجب منافذ SMTP، فيرسل الخادم بريده (رمز التحقق، إشعار البلاغ، رد الإدارة) عبر طلب HTTPS إلى **Google Apps Script** الذي يرسله من Gmail (حد يومي ≈ 100 رسالة)، وبعد إعادة الرد للتطبيق حتى لا يتأخر.
</details>

<details>
<summary><b>واجهة الـ API وهيكل المشروع</b></summary>

34 مساراً للتطبيق تحت `/api/v1` (مصادقة، مدن، أنواع أخطار، زلازل، طقس وتوقعات أسبوعية، من نحن، تنبيهات، بلاغات، إغاثة، هل شعرت بشيء، رموز الأجهزة). النصوص ثنائية اللغة (`ar`/`en`) ويرسل التطبيق `Accept-Language` لتصله رسائل الخطأ بلغته.

```
app/Services/        معالجة الزلازل والطقس، وإرسال التنبيهات وFCM، والبريد، وOTP
app/Filament/        لوحة الإدارة
app/Console/         أوامر Artisan (earthquake:listen, weather:fetch, ...)
config/              قواعد التنبيه (earthquake.php, weather_alerts.php)
docker/, Dockerfile  حاوية النشر
docs/                دليل النشر
```
للتجربة محلياً: `php artisan earthquake:simulate 6.2 SY006` يحاكي زلزالاً بقوة 6.2 قرب حمص.
</details>

<details>
<summary><b>النشر والحالة</b></summary>

حاوية Docker واحدة على Railway تشغّل بإدارة Supervisor: Nginx+PHP-FPM، `earthquake:listen`، `schedule:work`، وبجانبها MySQL وقرص دائم للصور. ويرسل `earthquake:listen` إشارة فحص كل 60 ثانية ويعيد الاتصال تلقائياً إذا صمت الاتصال أكثر من 180 ثانية.

**الحالة:** اختُبر على هاتف Android حقيقي (والتطبيق مفتوح/بالخلفية/الهاتف مقفل). لم تقع بعد عاصفة أو فيضان حقيقي على بيانات حية، فاختُبر منطقها بالمحاكاة. Android فقط (iOS خارج النطاق حالياً).
</details>

### ملاحظة
قواعد تحديد المحافظات وشدة الخطر **قواعد استهداف مبسطة وليست نموذجاً علمياً دقيقاً**، والمشروع مرحلي.

**مشروع مرحلي — جامعة الحواش الخاصة، كلية الهندسة، قسم المعلوماتية.**
الطلاب: محمد رمضان مصطفى دعبول، عبد المجيد دبدوب — بإشراف د. محمد ديب.

---

## 🇬🇧 English

An early warning system that detects **earthquakes, flash floods, river floods and storms** in Syria, works out **which governorates are affected**, and sends an instant alert to residents' phones, with an **alarm siren** for high severity and safety instructions in Arabic and English.

This repository is the **backend server** and admin panel. The mobile app (Flutter) lives in a separate project.

### How it works
1. The server receives earthquakes in real time from **EMSC** (permanent WebSocket) and fetches weather and river discharge hourly from **Open-Meteo**.
2. Simplified geographic and scientific rules decide which governorates are affected and how severe the risk is in each.
3. One alert is created per governorate and delivered to its users through **Firebase Cloud Messaging**.

### Key features
- Instant alerts that work even when the app is closed, with a siren for high severity
- Safety instructions before, during and after each hazard (Arabic / English)
- "Did you feel it?", user reports, and relief requests
- Email sign-up with OTP verification, or Google sign-in
- Admin panel (Filament) with two roles, admin and super admin, plus 2FA and an activity log

### Tech stack
PHP 8.2 · Laravel 12 · Sanctum · Filament · MySQL (SQLite for development) · Firebase Cloud Messaging · Docker · Railway

### Run locally
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan earthquake:listen   # separate terminal: earthquake feed
php artisan schedule:work       # separate terminal: hourly weather fetch
```
Requirements: PHP 8.2 with the `intl` extension. Push notifications need a Firebase service-account file (never committed). See `.env.example`.

### Deployment
The server runs on **Railway** inside a Docker container. Full guide: [docs/DEPLOY_RAILWAY.md](docs/DEPLOY_RAILWAY.md).

### Details

<details>
<summary><b>App features (Flutter)</b></summary>

- Interactive home screen: latest earthquake, weather for every governorate (the user's own first), today's event counts (Damascus time), and an "active warning" banner; tapping any item opens its details
- 7-day weather forecast for any governorate
- Alerts list with filtering by hazard type, and a history of past earthquakes
- Safety instructions (before / during / after) for each hazard type
- "Did you feel it?" using the Modified Mercalli scale (II–XII), user reports with status tracking, relief requests, and relief teams with their status and type
- Training danger simulator: siren, alert and safety instructions for a hazard type the user picks, with no data sent anywhere
- **Offline mode**: instructions, weather and alerts are cached and shown when the connection drops
- Arabic / English, dark / light theme, and notification and sound switches (applied on the server)
</details>

<details>
<summary><b>Alert rules</b></summary>

**Earthquakes:** an impact radius is estimated from the magnitude, then every governorate inside it is alerted, with severity dropping as distance from the epicenter grows (Haversine formula):

| Magnitude | Radius |
|---|---|
| 2.0 – 3.0 | 30 km |
| 3.0 – 4.0 | 60 km |
| 4.0 – 5.0 | 120 km |
| 5.0 – 6.0 | 220 km |
| 6.0 – 7.0 | 350 km |
| 7.0 and above | 500 km |

The radius is reduced by up to half for quakes deeper than 100 km.

The siren fires when magnitude ≥ 3.0 **and** severity is high/critical, which in practice means magnitude ≈ 5.5 or more at the nearest governorate.

**Tsunami:** epicenter in the sea facing the coast + magnitude ≥ 6.0 + depth ≤ 100 km → alert for Tartus and Latakia, with siren.

**Storms** (all three conditions must hold): wind + low pressure + rain ≥ 1 mm/h.

| | Inland `severe_storm` | Coastal `coastal_storm` |
|---|---|---|
| Wind | ≥ 60 km/h (critical ≥ 80) | ≥ 65 km/h (critical ≥ 90) |
| Pressure | ≤ 1000 hPa | ≤ 995 hPa |

"Storm" is used instead of "hurricane" because tropical cyclones do not form near the Syrian coast.

**Flash floods:** heavy-rain / thunderstorm weather codes, in the 9 governorates with valley terrain only.

**River floods:** Rastan Dam (Homs, Hama) by comparing discharge with the spillway capacity; the Euphrates (Al-Hasakah, Raqqa, Deir ez-Zor) by comparing discharge with its own 30-day median; the remaining rivers by high accumulated rain plus above-normal discharge.

**Cooldown:** the same weather alert is not repeated for the same governorate within 24 hours.

All numbers live in `config/earthquake.php` and `config/weather_alerts.php`.
</details>

<details>
<summary><b>Admin panel</b></summary>

At `/admin`; sign-in with email and password, then an authenticator-app code (mandatory).
- **Admin:** add and edit governorates and hazard types; view and edit alerts and earthquakes (they cannot be created by hand, because that would bypass the dispatch service); manage relief teams and their status; reply to reports (the reply is emailed to the user); delete users; view relief requests, "did you feel it" reports, weather readings, device tokens and alert deliveries.
- **Super admin:** everything above + **manual alert sending** (to one governorate or all, always critical with siren, with a confirmation dialog and a fixed prefix stating the category: test simulation / disaster the system missed / general national event) + **realistic disaster simulation** (physical values that go through the same real alert rules) + admin management + activity log.
- The original 16 governorates and 7 hazard types cannot be deleted; anything added later can only be deleted by a super admin after typing its name to confirm.
</details>

<details>
<summary><b>Security</b></summary>

- Passwords are hashed (bcrypt); policy: 8–24 English letters and digits
- The OTP is hashed and never written to the logs
- Sanctum tokens expire after 90 days and are revoked on logout
- 5 attempts per minute for login, registration, OTP and password reset (then 429)
- HTTPS and secure cookies; debug mode is off in production
- Secrets live in environment variables only and are never committed
</details>

<details>
<summary><b>Email</b></summary>

Railway blocks SMTP ports, so the server sends its email (verification code, report notice, admin reply) through an HTTPS request to a **Google Apps Script** that sends it from Gmail (daily limit ≈ 100 messages), after the response has already been returned to the app so it never slows the API down.
</details>

<details>
<summary><b>API and project structure</b></summary>

34 mobile routes under `/api/v1` (auth, cities, hazard types, earthquakes, weather and weekly forecast, about, alerts, reports, relief, "did you feel it", device tokens). Texts are bilingual (`ar`/`en`), and the app sends `Accept-Language` so error messages come back in its language.

```
app/Services/        earthquake and weather processing, alert dispatch and FCM, email, OTP
app/Filament/        admin panel
app/Console/         Artisan commands (earthquake:listen, weather:fetch, ...)
config/              alert rules (earthquake.php, weather_alerts.php)
docker/, Dockerfile  deployment container
docs/                deployment guide
```
To try it locally: `php artisan earthquake:simulate 6.2 SY006` simulates a magnitude 6.2 earthquake near Homs.
</details>

<details>
<summary><b>Deployment and status</b></summary>

One Docker container on Railway runs, under Supervisor: Nginx + PHP-FPM, `earthquake:listen` and `schedule:work`, alongside a MySQL service and a persistent volume for images. `earthquake:listen` pings every 60 s and reconnects automatically if the connection goes silent for over 180 s.

**Status:** tested on a real Android phone (app open / in background / phone locked). No real storm or flood has occurred yet on live data, so their logic was tested by simulation. Android only (iOS is out of scope for now).
</details>

### Note
The rules that pick affected governorates and severity are **simplified targeting heuristics, not an accurate scientific model**. This is a phased university project.

**Phased project — Al-Hawash Private University, Faculty of Engineering, Informatics Department.**
Students: Mohammad Ramadan Mustafa Dabool, Abdulmajeed Dabdoub — supervised by Dr. Mohammad Deeb.
