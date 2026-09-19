<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * "Hurricane" is retired as a concept — a genuine tropical cyclone can't
     * form near Syria's coast (see config/weather_alerts.php for the real
     * meteorological reasoning). The existing "hurricane" row is repurposed
     * in place (same id, so every existing alert/history stays correctly
     * linked) into "severe_storm" for inland wind+pressure+rain storms, and
     * a new "coastal_storm" row is added for the Mediterranean-coast case.
     */
    public function up(): void
    {
        DB::table('disaster_types')
            ->where('key', 'hurricane')
            ->update([
                'key' => 'severe_storm',
                'name_ar' => 'عاصفة شديدة',
                'name_en' => 'Severe Storm',
                'instructions_before_ar' => 'تابع نشرات الأرصاد الجوية باستمرار. أمّن الأشياء الخفيفة بالخارج وتفقد أسقف وشبابيك المنزل. اشحن الهواتف وجهّز إضاءة بديلة تحسباً لانقطاع الكهرباء، واحتفظ بمؤن كافية.',
                'instructions_before_en' => 'Monitor weather forecasts continuously. Secure loose outdoor items and inspect your roof and windows. Charge your phones, prepare backup lighting for possible power outages, and keep sufficient supplies on hand.',
                'instructions_during_ar' => 'ابقَ بالداخل بعيداً عن النوافذ والأبواب الزجاجية. تجنب استخدام الأجهزة الكهربائية غير الضرورية. لا تخرج إطلاقاً إلا للضرورة القصوى، وتجنب القيادة أثناء العاصفة.',
                'instructions_during_en' => 'Stay indoors, away from windows and glass doors. Avoid using non-essential electrical appliances. Do not go outside unless absolutely necessary, and avoid driving during the storm.',
                'instructions_after_ar' => 'احذر من الأسلاك الكهربائية الساقطة والأشجار المتضررة. أبلغ عن أي أضرار للجهات المختصة. انتظر الإعلان الرسمي بعودة الأوضاع لطبيعتها قبل التحرك بحرية.',
                'instructions_after_en' => 'Watch out for fallen power lines and damaged trees. Report any damage to the authorities. Wait for an official all-clear before moving around freely.',
                'updated_at' => now(),
            ]);

        DB::table('disaster_types')->insert([
            'key' => 'coastal_storm',
            'name_ar' => 'عاصفة ساحلية شديدة',
            'name_en' => 'Severe Coastal Storm',
            'instructions_before_ar' => 'تابع نشرات الأرصاد الجوية والتحذيرات البحرية. أعد القوارب الصغيرة إلى المرفأ وثبّتها جيداً، وأمّن الأشياء الخفيفة قرب الشاطئ والمنازل الساحلية. تجنب التخطيط لأي نشاط بحري أو صيد خلال فترة التحذير.',
            'instructions_before_en' => 'Monitor weather forecasts and marine warnings. Bring small boats back to harbor and secure them well, and secure loose items near the beach and coastal homes. Avoid planning any boating or fishing activity during the warning period.',
            'instructions_during_ar' => 'ابتعد فوراً عن الشاطئ والمرفأ والكورنيش البحري بسبب خطر الأمواج العالية والعواصف البحرية. ابقَ بالداخل بعيداً عن النوافذ المطلة على البحر. تجنب الصيد أو السباحة أو أي اقتراب من المياه، وانتبه لاحتمال فيضان المناطق الساحلية المنخفضة.',
            'instructions_during_en' => 'Move away from the beach, harbor, and seaside corniche immediately due to the risk of high waves and storm surge. Stay indoors, away from sea-facing windows. Avoid fishing, swimming, or approaching the water, and watch for possible flooding in low-lying coastal areas.',
            'instructions_after_ar' => 'لا تعد إلى الشاطئ أو المرفأ إلا بعد تأكيد الجهات المختصة زوال الخطر، فقد تستمر الأمواج العالية لساعات. تفقد القوارب والممتلكات الساحلية للتأكد من عدم تضررها، واحذر من الحطام والمياه على الطرقات الساحلية.',
            'instructions_after_en' => 'Do not return to the beach or harbor until authorities confirm the danger has passed, as high waves can persist for hours. Check boats and coastal property for damage, and watch out for debris and water on coastal roads.',
            'audio_file_ar' => null,
            'audio_file_en' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('disaster_types')->where('key', 'coastal_storm')->delete();

        DB::table('disaster_types')
            ->where('key', 'severe_storm')
            ->update([
                'key' => 'hurricane',
                'name_ar' => 'أعاصير',
                'name_en' => 'Hurricanes',
                'instructions_before_ar' => 'تابع نشرات الأرصاد الجوية باستمرار. أمّن الأشياء الخفيفة بالخارج وتفقد أسقف وشبابيك المنزل. اشحن الهواتف وجهّز إضاءة بديلة تحسباً لانقطاع الكهرباء، واحتفظ بمؤن كافية.',
                'instructions_before_en' => 'Monitor weather forecasts continuously. Secure loose outdoor items and inspect your roof and windows. Charge your phones, prepare backup lighting for possible power outages, and keep sufficient supplies on hand.',
                'instructions_during_ar' => 'ابقَ بالداخل بعيداً عن النوافذ والأبواب الزجاجية. تجنب استخدام الأجهزة الكهربائية غير الضرورية. لا تخرج إطلاقاً إلا للضرورة القصوى، وتجنب القيادة أثناء الإعصار.',
                'instructions_during_en' => 'Stay indoors, away from windows and glass doors. Avoid using non-essential electrical appliances. Do not go outside unless absolutely necessary, and avoid driving during the storm.',
                'instructions_after_ar' => 'احذر من الأسلاك الكهربائية الساقطة والأشجار المتضررة. أبلغ عن أي أضرار للجهات المختصة. انتظر الإعلان الرسمي بعودة الأوضاع لطبيعتها قبل التحرك بحرية.',
                'instructions_after_en' => 'Watch out for fallen power lines and damaged trees. Report any damage to the authorities. Wait for an official all-clear before moving around freely.',
                'updated_at' => now(),
            ]);
    }
};
