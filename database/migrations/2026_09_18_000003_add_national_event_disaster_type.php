<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A 7th disaster type for the super-admin manual alert tool: covers a
     * serious national situation that isn't a natural-disaster event at all
     * (so none of the existing 6 keys fit), e.g. a civil emergency. The
     * Flutter app already falls back to a neutral bell icon/color for any
     * unrecognized disaster-type key, so this needed no frontend change to
     * display correctly.
     */
    public function up(): void
    {
        DB::table('disaster_types')->insert([
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('disaster_types')->where('key', 'national_event')->delete();
    }
};
