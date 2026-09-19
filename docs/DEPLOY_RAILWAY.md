# دليل نشر الباك-إند على Railway

> هذا الملف لا يحتوي أي أسرار، فهو آمن داخل المستودع. القيم الحقيقية (كلمات السر والمفاتيح) تُكتب **فقط** في لوحة Railway.
> أسماء الأزرار في واجهة Railway قد تختلف قليلاً عمّا هو مكتوب هنا؛ الفكرة نفسها.

## ماذا سيعمل على Railway

خدمتان فقط:

| الخدمة | ماذا تفعل |
|---|---|
| **التطبيق** (من الـDockerfile) | حاوية واحدة تشغّل: الـAPI ولوحة الأدمن (nginx + php-fpm)، ومستمع الزلازل `earthquake:listen` (يبقى متصلاً بـEMSC)، والجدولة `schedule:work` (تحدّث الطقس كل ساعة). ويعيد Supervisor تشغيل أي عملية تتوقف. |
| **MySQL** | قاعدة البيانات (خدمة مستقلة من Railway). لا نستخدم SQLite لأن ملفات الحاوية تُمسح مع كل نشر جديد. |

## 0. قبل أن تبدأ: جهّز هذه القيم

على جهازك (في مجلد المشروع، PowerShell):

1. **مفتاح التطبيق `APP_KEY`** (جديد للإنتاج، لا تستخدم مفتاح جهازك):
   ```
   php artisan key:generate --show
   ```
   يطبع سطراً يبدأ بـ`base64:`. انسخه كاملاً. الأمر لا يغيّر ملف `.env`.
   **مهم جداً:** بعد أول تشغيل حقيقي **لا يجوز تغيير هذا المفتاح أبداً** (يفك تشفير جلسات اللوحة وسر المصادقة الثنائية للأدمن).

2. **مفتاح Firebase كنص** (بدل رفع الملف):
   ```
   [Convert]::ToBase64String([IO.File]::ReadAllBytes((Resolve-Path storage\app\firebase-service-account.json))) | Set-Clipboard
   ```
   لا يطبع شيئاً، لكنه ينسخ النص إلى الحافظة؛ الصقه في المتغير `FIREBASE_SERVICE_ACCOUNT_JSON` (الخطوة 3).

3. من ملف `.env` الحالي على جهازك انسخ هذه القيم (افتحه بمحرر نصوص، **ولا ترسل قيمها لأحد**):
   `MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`,
   `FIREBASE_PROJECT_ID`, `GOOGLE_OAUTH_CLIENT_ID`, `REPORTS_NOTIFICATION_EMAIL`.

4. اختر **كلمة سر للأدمن** لا تقل عن **12 حرفاً**.

## 1. إنشاء المشروع على Railway

1. ادخل `railway.com` ثم **New Project** ثم **Deploy from GitHub repo**.
2. عند ربط GitHub اختر **Only select repositories** وحدّد مستودع `early-warning-backend` فقط.
3. اختر المستودع. سيبدأ Railway بالبناء تلقائياً ويكتشف الـ`Dockerfile`. **سيفشل أو يتعثر أول تشغيل لأن المتغيرات غير موجودة بعد، وهذا متوقع**؛ لا تقلق منه، سنعيد النشر بعد ضبطها.

## 2. إضافة قاعدة MySQL

1. داخل المشروع: **New** (أو زر **+ Create**) ثم **Database** ثم **Add MySQL**.
2. اترك اسم الخدمة `MySQL` (الاسم مهم: تستعمله المتغيرات في الخطوة التالية).

## 3. متغيرات التطبيق

افتح **خدمة التطبيق** (وليس MySQL) ثم تبويب **Variables**. الأسهل: زر **Raw Editor** والصق الكتلة التالية بعد استبدال كل `<...>`:

```
PORT=8080
APP_NAME=EarlyWarning
APP_ENV=production
APP_DEBUG=false
APP_KEY=<القيمة من الخطوة 0-1>
APP_URL=https://<ضع دومين Railway لاحقاً>
APP_LOCALE=en
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
LOG_LEVEL=info

MAIL_MAILER=appsscript
APPSSCRIPT_MAIL_URL=<رابط Web App من القسم 3-ب>
APPSSCRIPT_MAIL_TOKEN=<الرمز السري من القسم 3-ب>
MAIL_FROM_ADDRESS=<من .env>
MAIL_FROM_NAME=<من .env>
REPORTS_NOTIFICATION_EMAIL=<من .env>

FIREBASE_PROJECT_ID=<من .env>
FIREBASE_SERVICE_ACCOUNT_JSON=<الصق الحافظة من الخطوة 0-2>
GOOGLE_OAUTH_CLIENT_ID=<من .env>

RUN_PRODUCTION_SEED=true
ADMIN_EMAIL=<بريدك الذي ستدخل به لوحة الأدمن>
ADMIN_NAME=<اسمك>
ADMIN_PASSWORD=<12 حرفاً على الأقل>
```

### 3-ب. البريد الإلكتروني على Railway (مهم: SMTP محجوب هناك)

Railway **يعطّل SMTP** (المنافذ 25 و465 و587) في الخطط Free وTrial وHobby (يعمل فقط في Pro). لذلك **لا يصلح** `MAIL_MAILER=smtp` ولا كلمة مرور تطبيق Gmail على Railway: كل رسالة تعلّق الطلب دقيقة كاملة ثم يفشل التطبيق بـ"Could not reach the server" (والحساب أو البلاغ قد يُحفظ ثم يتكرر عند إعادة المحاولة).

الحل المستخدم هنا: **وسيط Google Apps Script** (مجاني، بلا دومين). سيرفرنا يرسل طلب HTTPS إلى نص صغير عند حساب Gmail الخاص بالمشروع، والنص يرسل الرسالة من هذا الحساب. الحد التقريبي **100 رسالة يومياً** لحساب Gmail عادي.

1. ادخل `script.google.com` بحساب Gmail الذي تريد أن تخرج منه الرسائل، ثم **New project**.
2. امسح الكود الافتراضي والصق كود النص (محتوى ملف `early-warning-mail-script.gs.txt`) واحفظ. **يحتوي الملف رمزاً سرياً**، فلا يُرفع إلى GitHub ولا يُرسل لأحد.
3. **Deploy** ثم **New deployment** ثم النوع **Web app**، واضبط: *Execute as* = **Me**، *Who has access* = **Anyone**. ثم **Deploy**.
4. عند طلب الصلاحية: **Authorize access** ثم اختر حسابك ثم (تحذير Google "غير موثَّق" طبيعي لأنه نصك الخاص) **Advanced** ثم **Go to ... (unsafe)** ثم **Allow**.
5. انسخ **Web app URL** (ينتهي بـ`/exec`). افتحه في المتصفح: يجب أن ترى `{"ok":true,"service":"early-warning-mailer"}` (لا يرسل شيئاً).
6. في Railway (خدمة التطبيق ثم **Variables**) ضع:
   - `MAIL_MAILER=appsscript`
   - `APPSSCRIPT_MAIL_URL=` رابط الخطوة 5
   - `APPSSCRIPT_MAIL_TOKEN=` **نفس** الرمز السري الموجود في أول سطر من كود النص.
7. النشر ثم جرّب التسجيل بحساب جديد: يجب أن يصل رمز التحقق خلال ثوانٍ.

ملاحظات: الرمز السري هو الذي يمنع أي شخص يعرف رابط النص من استخدام حسابك للإرسال، فاحفظه ولا تنشره. وإن غيّرت الكود لاحقاً فاختر **Manage deployments ثم Edit ثم New version** (أو ابقَ على نفس الرابط). وإذا تخطى عدد الرسائل الحد اليومي فسيسجّل السيرفر خطأ في Logs بدل أن يعلّق التطبيق.

ملاحظات:
- `${{MySQL.MYSQLHOST}}` وأخواتها **تُكتب كما هي حرفياً**؛ Railway يستبدلها بقيم الخدمة `MySQL` تلقائياً.
- `SESSION_DRIVER=file` لأن جدول جلسات القاعدة غير موجود عندنا. (جلسات لوحة الأدمن تنتهي عند كل نشر جديد فتدخل من جديد، وهذا لا يمس مستخدمي التطبيق.)
- `LOG_CHANNEL=stderr` لتظهر السجلات في تبويب **Logs** في Railway.
- لا تضع في أي مكان: `FIREBASE_CREDENTIALS_PATH`، ولا `DB_URL`.

## 4. مساحة تخزين دائمة لصور الملفات الشخصية

1. في خدمة التطبيق: **Settings** ثم **Volumes** (أو **+ Add Volume**).
2. **Mount path:** `/var/www/html/storage/app/public`
3. بدون هذا الحجم تختفي الصور المرفوعة مع كل نشر.

## 5. الدومين ومسار الفحص

1. **Settings** ثم **Networking**. غالباً أنشأ Railway دومين تلقائياً؛ وإلا اضغط **Generate Domain** (يعطيك عنواناً مثل `xxxx.up.railway.app`، وفيه HTTPS جاهز).
2. **منفذ الدومين (مهم):** يجب أن يكون **8080**، وهو المنفذ الذي يستمع عليه nginx داخل الحاوية (المتغير `PORT=8080`). إن كان منفذ الدومين مختلفاً ظهر الخطأ `502 Application failed to respond` رغم أن السجلات تقول إن كل شيء يعمل. عدّله من سطر الدومين نفسه.
3. ارجع إلى المتغيرات وضع: `APP_URL=https://xxxx.up.railway.app`
3. **Settings** ثم **Deploy** ثم **Healthcheck Path** = `/up`
4. اضغط **Deploy** (أو Railway سيعيد النشر تلقائياً عند تغيير المتغيرات).

## 6. ماذا يجب أن ترى في Logs (علامات النجاح)

في تبويب **Deployments** ثم آخر نشر ثم **Logs**:

- `Configuration cached successfully.` و`Blade templates cached successfully.`
- قائمة الـmigrations (`... DONE`) للمرة الأولى.
- `ProductionSeeder`: ثلاث `DONE`.
- `Super admin created: <بريدك>.`
- `success: php-fpm entered RUNNING state` و`nginx` و`earthquake-listener` و`scheduler`.

إن ظهر في السجل `WARNING: ... failed` فاقرأ الأسطر التي فوقه لتعرف السبب (غالباً كلمة سر أقصر من 12 حرفاً أو بريد غير صالح).

## 7. أول دخول للوحة الأدمن

1. افتح `https://xxxx.up.railway.app/admin` وادخل ببريدك وكلمة السر.
2. ستُحوَّل مباشرة لإعداد **المصادقة الثنائية**: امسح رمز QR بتطبيق Google Authenticator، واكتب الرمز، و**احفظ رموز الاسترداد الثمانية في مكان آمن خارج الحاسوب**.
3. **بعد نجاح الدخول** ارجع لمتغيرات Railway و**احذف `ADMIN_PASSWORD`** (وغيّر `RUN_PRODUCTION_SEED` إلى `false`). تركهما لا يكسر شيئاً لكنه يترك كلمة سر ظاهرة.

## 8. التحقق من أن كل شيء يعمل

| الفحص | المتوقع |
|---|---|
| `https://…/up` | 200 |
| `https://…/api/v1/cities` | JSON فيه 16 محافظة بالعربية |
| لوحة الأدمن `/admin` | تفتح بشكلها الكامل (الألوان والأيقونات) |
| لوحة الأدمن ثم **Send Alert** لتنبيه تجريبي | يصل لأجهزة المدينة (بعد ربط التطبيق، الخطوة 9) |
| سجل Logs | يظهر `earthquake-listener` مستمراً بلا انهيار متكرر |
| الطقس | يتحدث تلقائياً **عند أول ساعة كاملة** بعد التشغيل (الجدولة `hourly`) |

## 9. ربط تطبيق Flutter بالسيرفر الجديد

هذه الخطوة تمس ملف Flutter، فلا تُنفَّذ إلا بموافقتك الصريحة:

1. في `lib/api_client.dart` تغيير `ApiConfig.baseUrl` إلى `https://xxxx.up.railway.app` وإزالة هيدر `ngrok-skip-browser-warning` المؤقت.
2. إعادة بناء التطبيق عند الصديق.
3. **لا حاجة لتغيير Firebase ولا بصمة SHA-1** (نفس المشروع ونفس المفتاح).
4. المستخدمون الحاليون في قاعدة SQLite المحلية **لا ينتقلون** للسيرفر الجديد، فالقاعدة جديدة وفارغة؛ يسجَّل الجميع من جديد.

## 10. حلول للمشاكل الشائعة

| العَرَض | السبب المحتمل | الحل |
|---|---|---|
| البناء يفشل عند `composer` أو `apt-get` | خطأ في الـDockerfile أو انقطاع شبكة | انسخ آخر 30 سطراً من سجل البناء وأرسلها لي |
| `Access denied` أو `Connection refused` للقاعدة | متغيرات `DB_*` خاطئة أو اسم خدمة القاعدة ليس `MySQL` | تأكد من الكتلة كما هي، وأن الخدمة اسمها `MySQL` |
| الحاوية تعيد التشغيل باستمرار | فشل `migrate` (السجل يقول `giving up`) | اقرأ سبب الخطأ فوقه في Logs |
| لوحة الأدمن بدون ألوان أو تنسيق | `APP_URL` ليس `https://…` الصحيح | صحّح `APP_URL` وأعد النشر |
| `419 Page Expired` عند دخول اللوحة | الكوكيز أو الجلسة | تأكد من `SESSION_DRIVER=file` و`SESSION_SECURE_COOKIE=true` و`APP_URL` |
| لا تصل رسائل OTP بالبريد، أو "Could not reach the server" عند التسجيل | SMTP محجوب في Railway، أو إعداد `appsscript` ناقص | استخدم `MAIL_MAILER=appsscript` (القسم 3-ب). ابحث في Logs عن `Failed to send OTP email` وسبب الرفض بعده |
| لا تصل إشعارات الدفع | `FIREBASE_SERVICE_ACCOUNT_JSON` أو `FIREBASE_PROJECT_ID` | أعد نسخ النص من الحافظة كاملاً؛ ابحث في Logs عن `FCM:` |
| صور الملف الشخصي تختفي بعد نشر جديد | الحجم الدائم غير مربوط | الخطوة 4 |
| 429 بعد محاولات قليلة | حد المحاولات (5 في الدقيقة) | طبيعي؛ الانتظار دقيقة |

## 11. أمور يجب ألا تنساها

- **`APP_KEY` لا يتغير أبداً** بعد أول تشغيل حقيقي، وخذ نسخة منه في مكان آمن.
- كل ما هو سري (كلمات السر والمفاتيح) **في Railway فقط**، لا في GitHub ولا في هذا الملف.
- بعد نجاح الدخول: احذف `ADMIN_PASSWORD`.
- الرصيد التجريبي في Railway محدود (**5 دولارات لمدة 30 يوماً بدون بطاقة** حسب صفحة أسعارهم عند كتابة هذا الدليل). راقب صفحة الاستهلاك، وإذا انتهى الرصيد يتوقف كل شيء.
- للعودة إلى التشغيل المحلي: لا شيء يتغير عندك؛ ملف `.env` المحلي وقاعدة SQLite ونفق ngrok كما هي.
