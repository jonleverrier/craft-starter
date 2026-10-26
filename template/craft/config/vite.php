<?php

use craft\helpers\App;

return [
    'checkDevServer' => true,
    'devServerInternal' => 'http://localhost:3000',
    'devServerPublic' => App::env('URL_SITE_PUBLIC') . ':3000',
    'useDevServer' => App::env('CRAFT_ENVIRONMENT') === 'dev',
    'manifestPath' => Craft::getAlias('@webroot') . '/dist/manifest.json',
    // Where rollup-plugin-critical writes (criticalBase in vite.config.js).
    'criticalPath' => Craft::getAlias('@webroot') . '/dist/criticalcss',
    // Root-relative, so Vite's loader recognises the head's stylesheet and doesn't add it twice.
    'serverPublic' => '/dist/',
    'errorEntry' => 'build/js/app.js',
    'includeModulePreloadShim' => true,
    'includeReactRefreshShim' => false,
];
