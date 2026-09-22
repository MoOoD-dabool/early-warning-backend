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
    | 'active'/'en_route' are a team actually doing something right now.
    | 'completed' is also shown — a citizen seeing "mission completed" is
    | real, reassuring confirmation the system responded, not just noise.
    | 'inactive' stays hidden: it's an internal admin/roster state (not yet
    | dispatched, off duty, withdrawn) that doesn't tell a citizen anything
    | useful and could misread as "no help available" during a real event.
    */
    'user_visible_statuses' => ['active', 'en_route', 'completed'],

    'types' => [
        'medical' => ['label_ar' => 'طبي', 'label_en' => 'Medical'],
        'food_water' => ['label_ar' => 'غذاء ومياه', 'label_en' => 'Food & Water'],
        'shelter' => ['label_ar' => 'إيواء', 'label_en' => 'Shelter'],
        'search_rescue' => ['label_ar' => 'إنقاذ وبحث', 'label_en' => 'Search & Rescue'],
        'logistics' => ['label_ar' => 'دعم لوجستي', 'label_en' => 'Logistics Support'],
        'other' => ['label_ar' => 'أخرى', 'label_en' => 'Other'],
    ],

];
