<?php
/**
 * General Configuration
 *
 * @see \craft\config\GeneralConfig
 * @link https://craftcms.com/docs/5.x/reference/config/general.html
 */

use craft\config\GeneralConfig;
use craft\helpers\App;

$config = GeneralConfig::create()
    ->defaultWeekStartDay(1)
    ->omitScriptNameInUrls()
    ->preloadSingles()
    ->preventUserEnumeration()
    ->aliases([
        '@web' => App::env('URL_SITE_PUBLIC'),
        '@webroot' => App::env('PRIMARY_SITE_WEBROOT'),
    ])
    ->defaultCpLanguage('en-GB')
    ->defaultCpLocale('en-GB')
    ->cpTrigger('manager')
    // Error templates live with the views: Craft looks for _views/<code>, then _views/error.
    ->errorTemplatePrefix('_views/')
    ->sendPoweredByHeader(false)
    // ?v=<timestamp> on asset URLs, so a file replaced in the CP isn't served stale from caches.
    ->revAssetUrls()
    // Off until something needs it: no unwatched query endpoint.
    ->enableGql(false)
    // An "SVG" file kind, so an Assets field can be limited to SVG only.
    ->extraFileKinds([
        'svg' => [
            'label' => 'SVG',
            'extensions' => ['svg'],
        ],
    ])
    // Send the page straight away; build each transform when a browser asks for it.
    ->generateTransformsBeforePageLoad(false)
    // Never enlarge a source to fill a transform — bigger files, no more detail.
    ->upscaleImages(false)
;

// Dev only: PHP and DDEV's nginx already allow bigger uploads; production keeps Craft's default.
if (App::env('CRAFT_ENVIRONMENT') === 'dev') {
    $config->maxUploadFileSize('50M');
}

return $config;
