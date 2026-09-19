<?php

/*
|--------------------------------------------------------------------------
| Modified Mercalli Intensity scale — "Did You Feel It" feature
|--------------------------------------------------------------------------
| Level I ("not felt") is deliberately excluded since this feature only
| makes sense for something a user actually felt. Each remaining level's
| bilingual text is shown to the user as a selectable option; the user
| picks between 1 and 3 levels that best match what they personally
| experienced during a real, recent earthquake.
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Eligibility window
    |--------------------------------------------------------------------------
    | How many hours after a real earthquake a user can still submit a felt
    | report for it. Kept generous since realizing an earthquake happened,
    | opening the app, and deciding to report it can take a user a while
    | during a real disaster.
    */
    'eligible_window_hours' => 48,

    'levels' => [
        2 => [
            'roman' => 'II',
            'label_ar' => 'بالكاد يُدرك',
            'label_en' => 'Scarcely felt',
        ],
        3 => [
            'roman' => 'III',
            'label_ar' => 'اهتزازات مشابهة لشاحنة تمر بالجوار',
            'label_en' => 'Vibrations similar to a passing truck',
        ],
        4 => [
            'roman' => 'IV',
            'label_ar' => 'اهتزاز النوافذ، اهتزاز خفيف للأشياء المعلقة',
            'label_en' => 'Windows rattling, slight shaking of hanging objects',
        ],
        5 => [
            'roman' => 'V',
            'label_ar' => 'سقوط لأغراض عن الرفوف',
            'label_en' => 'Objects falling from shelves',
        ],
        6 => [
            'roman' => 'VI',
            'label_ar' => 'شقوق صغيرة في المباني وانكسار الشبابيك',
            'label_en' => 'Small cracks in buildings, broken windows',
        ],
        7 => [
            'roman' => 'VII',
            'label_ar' => 'أضرار في المباني وصعوبة على أن تبقى واقفة',
            'label_en' => 'Damage to buildings, difficulty remaining standing',
        ],
        8 => [
            'roman' => 'VIII',
            'label_ar' => 'أضرار واضحة في المباني وانهيارات جزئية',
            'label_en' => 'Visible damage to buildings, partial collapses',
        ],
        9 => [
            'roman' => 'IX',
            'label_ar' => 'انهيارات مستمرة في المباني، ووقوع الناس على الأرض',
            'label_en' => 'Continuous building collapses, people falling to the ground',
        ],
        10 => [
            'roman' => 'X',
            'label_ar' => 'انهيار مبانٍ كاملة',
            'label_en' => 'Complete collapse of buildings',
        ],
        11 => [
            'roman' => 'XI',
            'label_ar' => 'تدمير مراكز حضرية بأكملها، العديد من الضحايا، شقوق في الأرض وانهيارات أرضية',
            'label_en' => 'Destruction of entire urban centers, numerous casualties, ground cracks and landslides',
        ],
        12 => [
            'roman' => 'XII',
            'label_ar' => 'اضطراب التربة، إزاحة قشرة الأرض',
            'label_en' => "Soil disturbance, displacement of the earth's crust",
        ],
    ],

];
