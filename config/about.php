<?php

/*
|--------------------------------------------------------------------------
| "About Us" screen content
|--------------------------------------------------------------------------
| Static bilingual text for the app's About Us screen, served via
| GET /about so it can be updated with a backend deploy only — no new
| Flutter build/release needed. Deliberately kept as plain code (not an
| admin-editable database record): this content changes rarely enough
| that a full admin UI for it wasn't judged worth the extra engineering.
*/

return [

    'title' => [
        'ar' => 'نحن:',
        'en' => 'About Us:',
    ],

    'body' => [
        'ar' => "تم بناء وتطوير هذا التطبيق بجهد الطالبَين محمد رمضان مصطفى دعبول وعبد المجيد دبدوب، بإشراف ومتابعة الدكتور محمد ديب، فله منا أعظم الشكر والتقدير.\n\nراجين أن يحقق هذا العمل تطلعاتنا للمستقبل، وأن يكون خير عون لأقطاب الشعب السوري كافة.",
        'en' => "This app was built and developed by students Mohammed Ramadan Mustafa Dabool and Abdulmajeed Dabdoub, under the supervision and guidance of Dr. Mohammed Deeb — to whom we owe our deepest gratitude and appreciation.\n\nWe hope this work lives up to our hopes for the future, and that it becomes a true source of support for the Syrian people, in all their diversity.",
    ],

];
