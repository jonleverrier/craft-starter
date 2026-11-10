<?php
/**
 * Yii Application Config — still read on Craft 6 through craftcms/yii2-adapter.
 *
 * Top-level keys are CRAFT_ENVIRONMENT names, not request types.
 *
 * @link https://craftcms.com/docs/6.x/reference/config/app.html
 */

return [
    '*' => [
        'id' => env('CRAFT_APP_ID') ?: 'CraftCMS',
        'modules' => [
            'frontend' => \modules\frontend\FrontEnd::class,
        ],
        'bootstrap' => ['frontend'],
        'components' => [
            // Craft's image service plus two AVIF encoder options (see the class).
            'images' => \modules\frontend\services\Images::class,
        ],
    ],
];
