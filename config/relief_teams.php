<?php

/*
|--------------------------------------------------------------------------
| Relief teams — status & relief-type vocabularies
|--------------------------------------------------------------------------
| Both are small, fixed sets (like the Mercalli levels in config/mercalli.php)
| rather than free bilingual text columns on every relief_teams row — an
| admin picks one of these keys, and the API returns its bilingual label
| the same way every other bilingual field in this app is returned
| ({ "ar": ..., "en": ... }), without needing a name_ar/name_en pair per row.
*/

return [

    'statuses' => [
        'active' => ['label_ar' => 'نشط', 'label_en' => 'Active'],
        'en_route' => ['label_ar' => 'في الطريق', 'label_en' => 'En Route'],
        'completed' => ['label_ar' => 'مهمة مكتملة', 'label_en' => 'Mission Completed'],
        'inactive' => ['label_ar' => 'متوقف', 'label_en' => 'Inactive'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Statuses shown to mobile users
    |--------------------------------------------------------------------------
    | Only a team that's actually doing something right now (active or on its
    | way) is useful information for a citizen looking for help. 'completed'
    | and 'inactive' are history the admin panel keeps, not something the
    | mobile app's public /relief-teams endpoint should ever surface.
    */
    'user_visible_statuses' => ['active', 'en_route'],

    'types' => [
        'medical' => ['label_ar' => 'طبي', 'label_en' => 'Medical'],
        'food_water' => ['label_ar' => 'غذاء ومياه', 'label_en' => 'Food & Water'],
        'shelter' => ['label_ar' => 'إيواء', 'label_en' => 'Shelter'],
        'search_rescue' => ['label_ar' => 'إنقاذ وبحث', 'label_en' => 'Search & Rescue'],
        'logistics' => ['label_ar' => 'دعم لوجستي', 'label_en' => 'Logistics Support'],
        'other' => ['label_ar' => 'أخرى', 'label_en' => 'Other'],
    ],

];
