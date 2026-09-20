<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DisasterType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DisasterTypeSeeder extends Seeder
{
    /**
     * The 5 disaster types this app covers, with real bilingual
     * (Arabic/English) precautions. The app defaults to Arabic and switches
     * to English from the settings/theme screen — both are always returned
     * so the app can switch instantly without another request.
     */
    private const DISASTER_TYPES = [
        [
            'key' => 'earthquake',
            'name_ar' => 'زلازل',
            'name_en' => 'Earthquakes',
            'instructions_before_ar' => 'جهّز حقيبة طوارئ فيها ماء، إسعافات أولية، بطارية إضاءة، ووثائق مهمة. حدّد أماكن آمنة في المنزل (تحت طاولة متينة، بعيداً عن الزجاج). تعرّف على مخارج الطوارئ في المبنى ونقاط التجمع في الحي.',
            'instructions_before_en' => 'Prepare an emergency kit with water, first aid supplies, a flashlight, and important documents. Identify safe spots at home (under a sturdy table, away from glass). Know your building\'s emergency exits and your neighborhood\'s assembly points.',
            'instructions_during_ar' => 'انزل أرضاً واحتمِ تحت طاولة متينة وامسك بها حتى يهدأ الاهتزاز. ابتعد عن النوافذ والمرايا والأثاث الثقيل. إذا كنت في الخارج، ابتعد عن المباني والأعمدة الكهربائية. لا تستخدم المصعد.',
            'instructions_during_en' => 'Drop to the ground and take cover under a sturdy table, holding on until the shaking stops. Stay away from windows, mirrors, and heavy furniture. If outdoors, move away from buildings and power lines. Never use elevators.',
            'instructions_after_ar' => 'تحقق من إصابتك وإصابة من حولك وقدّم الإسعافات الأولية إذا لزم. افحص وجود تسربات غاز أو أضرار كهربائية قبل استخدام أي جهاز. توقّع هزات ارتدادية وابتعد عن المباني المتضررة. اتبع تعليمات الدفاع المدني.',
            'instructions_after_en' => 'Check yourself and others for injuries and give first aid if needed. Check for gas leaks or electrical damage before using any appliance. Expect aftershocks and stay away from damaged buildings. Follow civil defense instructions.',
            'audio_file_ar' => null,
            'audio_file_en' => null,
        ],
        [
            'key' => 'flash_flood',
            'name_ar' => 'سيول',
            'name_en' => 'Flash Floods',
            'instructions_before_ar' => 'تابع نشرات الطقس والتحذيرات الرسمية. تجنب السكن أو ركن السيارة قرب مجاري الأودية. جهّز حقيبة طوارئ واعرف أقرب منطقة مرتفعة آمنة.',
            'instructions_before_en' => 'Follow weather updates and official warnings. Avoid living or parking near valley/stream beds. Prepare an emergency kit and know the nearest safe high ground.',
            'instructions_during_ar' => 'انتقل فوراً لمكان مرتفع وابتعد عن مجاري السيول والأودية. لا تحاول عبور المياه سيراً أو بالسيارة مهما بدت ضحلة، فالسيول قد تجرف السيارات بسرعة. افصل الكهرباء عن المنزل إذا كان ذلك آمناً.',
            'instructions_during_en' => 'Move immediately to higher ground and stay away from stream and valley beds. Never try to cross moving water on foot or by car, even if it looks shallow — flash floods can sweep cars away quickly. Turn off home electricity if it is safe to do so.',
            'instructions_after_ar' => 'لا تشرب مياه قد تكون ملوثة بمياه السيول. تجنب المناطق المتضررة حتى التأكد من سلامتها. أبلغ الجهات المختصة عن أي طرق أو جسور متضررة.',
            'instructions_after_en' => 'Do not drink water that may be contaminated by floodwater. Avoid affected areas until confirmed safe. Report any damaged roads or bridges to the authorities.',
            'audio_file_ar' => null,
            'audio_file_en' => null,
        ],
        [
            'key' => 'flood',
            'name_ar' => 'فيضانات',
            'name_en' => 'Floods',
            'instructions_before_ar' => 'تابع نشرات الطقس والتحذيرات الرسمية. ارفع الأجهزة الكهربائية والمستندات المهمة عن مستوى الأرض. جهّز حقيبة طوارئ واعرف أقرب منطقة مرتفعة آمنة.',
            'instructions_before_en' => 'Follow weather updates and official warnings. Raise electrical appliances and important documents above ground level. Prepare an emergency kit and know the nearest safe high ground.',
            'instructions_during_ar' => 'انتقل فوراً لمكان مرتفع وابتعد عن مصادر المياه المرتفعة. لا تحاول عبور المياه سيراً أو بالسيارة مهما بدت ضحلة. افصل الكهرباء عن المنزل إذا كان ذلك آمناً.',
            'instructions_during_en' => 'Move to higher ground immediately and stay away from rising water. Never try to cross moving water on foot or by car, even if it looks shallow. Turn off home electricity if it is safe to do so.',
            'instructions_after_ar' => 'لا تشرب مياه قد تكون ملوثة بمياه الفيضان. تجنب المناطق المتضررة حتى التأكد من سلامتها. نظّف وطهّر أي أسطح لامست مياه الفيضان قبل استخدامها.',
            'instructions_after_en' => 'Do not drink water that may be contaminated by floodwater. Avoid affected areas until confirmed safe. Clean and disinfect any surfaces that came into contact with floodwater before use.',
            'audio_file_ar' => null,
            'audio_file_en' => null,
        ],
        [
            'key' => 'severe_storm',
            'name_ar' => 'عاصفة شديدة',
            'name_en' => 'Severe Storm',
            'instructions_before_ar' => 'تابع نشرات الأرصاد الجوية باستمرار. أمّن الأشياء الخفيفة في الخارج وتفقد أسقف وشبابيك المنزل. اشحن الهواتف وجهّز إضاءة بديلة تحسباً لانقطاع الكهرباء، واحتفظ بمؤن كافية.',
            'instructions_before_en' => 'Monitor weather forecasts continuously. Secure loose outdoor items and inspect your roof and windows. Charge your phones, prepare backup lighting for possible power outages, and keep sufficient supplies on hand.',
            'instructions_during_ar' => 'ابقَ في الداخل بعيداً عن النوافذ والأبواب الزجاجية. تجنب استخدام الأجهزة الكهربائية غير الضرورية. لا تخرج إطلاقاً إلا للضرورة القصوى، وتجنب القيادة أثناء العاصفة.',
            'instructions_during_en' => 'Stay indoors, away from windows and glass doors. Avoid using non-essential electrical appliances. Do not go outside unless absolutely necessary, and avoid driving during the storm.',
            'instructions_after_ar' => 'احذر من الأسلاك الكهربائية الساقطة والأشجار المتضررة. أبلغ عن أي أضرار للجهات المختصة. انتظر الإعلان الرسمي بعودة الأوضاع لطبيعتها قبل التحرك بحرية.',
            'instructions_after_en' => 'Watch out for fallen power lines and damaged trees. Report any damage to the authorities. Wait for an official all-clear before moving around freely.',
            'audio_file_ar' => null,
            'audio_file_en' => null,
        ],
        [
            'key' => 'coastal_storm',
            'name_ar' => 'عاصفة ساحلية شديدة',
            'name_en' => 'Severe Coastal Storm',
            'instructions_before_ar' => 'تابع نشرات الأرصاد الجوية والتحذيرات البحرية. أعد القوارب الصغيرة إلى المرفأ وثبّتها جيداً، وأمّن الأشياء الخفيفة قرب الشاطئ والمنازل الساحلية. تجنب التخطيط لأي نشاط بحري أو صيد خلال فترة التحذير.',
            'instructions_before_en' => 'Monitor weather forecasts and marine warnings. Bring small boats back to harbor and secure them well, and secure loose items near the beach and coastal homes. Avoid planning any boating or fishing activity during the warning period.',
            'instructions_during_ar' => 'ابتعد فوراً عن الشاطئ والمرفأ والكورنيش البحري بسبب خطر الأمواج العالية والعواصف البحرية. ابقَ في الداخل بعيداً عن النوافذ المطلة على البحر. تجنب الصيد أو السباحة أو أي اقتراب من المياه، وانتبه لاحتمال فيضان المناطق الساحلية المنخفضة.',
            'instructions_during_en' => 'Move away from the beach, harbor, and seaside corniche immediately due to the risk of high waves and storm surge. Stay indoors, away from sea-facing windows. Avoid fishing, swimming, or approaching the water, and watch for possible flooding in low-lying coastal areas.',
            'instructions_after_ar' => 'لا تعد إلى الشاطئ أو المرفأ إلا بعد تأكيد الجهات المختصة زوال الخطر، فقد تستمر الأمواج العالية لساعات. تفقد القوارب والممتلكات الساحلية للتأكد من عدم تضررها، واحذر من الحطام والمياه على الطرقات الساحلية.',
            'instructions_after_en' => 'Do not return to the beach or harbor until authorities confirm the danger has passed, as high waves can persist for hours. Check boats and coastal property for damage, and watch out for debris and water on coastal roads.',
            'audio_file_ar' => null,
            'audio_file_en' => null,
        ],
        [
            'key' => 'tsunami',
            'name_ar' => 'تسونامي',
            'name_en' => 'Tsunami',
            'instructions_before_ar' => 'تعرّف إذا كانت منطقتك ساحلية معرّضة لخطر التسونامي ومسارات الإخلاء المحددة لها. اعرف إشارات الإنذار الطبيعية: زلزال قوي قرب الساحل، أو انحسار مفاجئ للبحر، أو صوت هدير غير معتاد من البحر.',
            'instructions_before_en' => 'Know whether your area is a coastal zone at tsunami risk and its designated evacuation routes. Learn the natural warning signs: a strong coastal earthquake, a sudden, unusual retreat of the sea, or an unusual roaring sound from the ocean.',
            'instructions_during_ar' => 'إذا شعرت بهزة أرضية قوية قرب الساحل أو رأيت البحر ينحسر فجأة، لا تنتظر إنذاراً رسمياً، وتوجه فوراً لأعلى منطقة ممكنة بعيداً عن الساحل سيراً على الأقدام إن أمكن. ابتعد عن الشاطئ والأنهار القريبة من البحر.',
            'instructions_during_en' => 'If you feel a strong coastal earthquake or see the sea suddenly recede, do not wait for an official warning — head immediately to the highest ground possible, away from the coast, on foot if possible. Stay away from the beach and rivers near the sea.',
            'instructions_after_ar' => 'لا تعد للمناطق الساحلية إلا بعد تأكيد الجهات المختصة أن الخطر زال، فقد تتكرر الموجات لساعات. احذر من الأنقاض والمياه الملوثة، وأبلغ عن المفقودين للدفاع المدني.',
            'instructions_after_en' => 'Do not return to coastal areas until authorities confirm the danger has passed, as waves can recur for hours. Beware of debris and contaminated water, and report missing persons to civil defense.',
            'audio_file_ar' => null,
            'audio_file_en' => null,
        ],
        [
            'key' => 'national_event',
            'name_ar' => 'حدث وطني عام',
            'name_en' => 'General National Event',
            'instructions_before_ar' => 'تابع القنوات الرسمية ووسائل الإعلام الموثوقة للحصول على آخر المستجدات. لا تصدّق أو تنشر أي إشاعات غير مؤكدة. تأكد من شحن هاتفك وتجهيز وسيلة اتصال احتياطية.',
            'instructions_before_en' => 'Follow official channels and trusted media for the latest updates. Do not believe or spread unconfirmed rumors. Make sure your phone is charged and you have a backup way to stay in contact.',
            'instructions_during_ar' => 'اتبع تعليمات الجهات الرسمية المختصة بدقة. ابقَ هادئاً وتجنب التجمعات الكبيرة إلا إذا طُلب منك ذلك رسمياً. حافظ على تواصلك مع أفراد عائلتك وأبلغهم بمكانك.',
            'instructions_during_en' => 'Follow the instructions of the relevant official authorities carefully. Stay calm and avoid large gatherings unless officially instructed otherwise. Stay in contact with your family and let them know your location.',
            'instructions_after_ar' => 'انتظر الإعلان الرسمي بانتهاء الحالة قبل العودة لنشاطك المعتاد. تابع أي تعليمات إضافية من الجهات المختصة. أبلغ عن أي معلومات مهمة تخص سلامتك أو سلامة من حولك للجهات الرسمية.',
            'instructions_after_en' => 'Wait for an official announcement that the situation has ended before resuming your normal activity. Follow any additional instructions from the relevant authorities. Report any important information about your safety or the safety of those around you to the official authorities.',
            'audio_file_ar' => null,
            'audio_file_en' => null,
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('disaster_types')) {
            return;
        }

        foreach (self::DISASTER_TYPES as $type) {
            DisasterType::query()->updateOrCreate(
                ['key' => $type['key']],
                $type,
            );
        }
    }
}