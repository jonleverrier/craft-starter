<?php
/**
 * General Configuration
 *
 * An array, not GeneralConfig::create()->…: Laravel loads this file before Craft boots,
 * and some setters need Craft running. Craft applies each key through its setter later.
 *
 * @see \CraftCms\Cms\Config\GeneralConfig
 * @link https://craftcms.com/docs/6.x/reference/config/general.html
 */

return [
    'defaultWeekStartDay' => 1,
    'preloadSingles' => true,
    'preventUserEnumeration' => true,
    'aliases' => [
        '@web' => env('URL_SITE_PUBLIC'),
        '@webroot' => public_path(),
    ],
    // Not defaultCpLanguage: on 6.0.0-beta.1 it loops (normalising the language reads this
    // config while it's loading) until PHP runs out of memory. Add it back once that's fixed.
    'defaultCpLocale' => 'en-GB',
    'cpTrigger' => 'manager',
    'sendPoweredByHeader' => false,
    // ?v=<timestamp> on asset URLs, so a file replaced in the CP isn't served stale from caches.
    'revAssetUrls' => true,
    // Off until something needs it: no unwatched query endpoint.
    'enableGql' => false,
    // An "SVG" file kind, so an Assets field can be limited to SVG only.
    'extraFileKinds' => [
        'svg' => [
            'label' => 'SVG',
            'extensions' => ['svg'],
        ],
    ],
    // Never enlarge a source to fill a transform — bigger files, no more detail.
    'upscaleImages' => false,
    // Dev only: PHP and DDEV's nginx already allow bigger uploads; production keeps Craft's default.
    ...(env('CRAFT_ENVIRONMENT') === 'dev' ? ['maxUploadFileSize' => '50M'] : []),
];

// Error pages: Craft 6 looks for errors/<code>, then errors/error (see resources/views/errors/).
