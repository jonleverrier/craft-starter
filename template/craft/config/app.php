<?php
/**
 * Yii Application Config
 *
 * Top-level keys are CRAFT_ENVIRONMENT names, not request types. Web-only components go
 * in app.web.php.
 *
 * @link https://craftcms.com/docs/5.x/reference/config/app.html
 */

use craft\helpers\App;

return [
    '*' => [
        'id' => App::env('CRAFT_APP_ID') ?: 'CraftCMS',
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
