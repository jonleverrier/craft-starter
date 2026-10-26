<?php

namespace modules\frontend\variables;

use Craft;
use nystudio107\vite\Vite;

/**
 * Template helpers exposed as `craft.frontend`.
 */
class FrontEndVariable
{
    /** Cookie recording which build's critical CSS the visitor already has. */
    private const CRITICAL_COOKIE = 'criticalcss';

    /** Roughly three months. */
    private const CRITICAL_COOKIE_LIFETIME = 7890000;

    /** A month: AVIF support is a property of the host. */
    private const AVIF_TTL = 2592000;

    /** AVIF quality, separate from the WebP/JPEG quality. */
    private const AVIF_QUALITY = 72;

    /**
     * Has this visitor already been served the current build's critical CSS?
     *
     * A miss also SETS the cookie, so the next page load is a hit. The cookie holds the
     * CSS hash, so a new build invalidates it on its own.
     */
    public function hasCriticalCssCookie(): bool
    {
        // Empty if the plugin is missing or nothing is built: then critical CSS is always inlined.
        $hash = Vite::$plugin?->helper->getCssHash('build/js/app.js') ?? '';

        if (($_COOKIE[self::CRITICAL_COOKIE] ?? null) === $hash) {
            return true;
        }

        // Raw, not through Craft's signed cookies, so JS or an edge cache can read it.
        setcookie(self::CRITICAL_COOKIE, $hash, [
            'expires' => time() + self::CRITICAL_COOKIE_LIFETIME,
            'path' => '/',
            // Secure cookies are dropped on plain http.
            'secure' => Craft::$app->getRequest()->getIsSecureConnection(),
            'samesite' => 'Lax',
        ]);

        return false;
    }

    /**
     * Can this host really encode AVIF? Checked by encoding a 1×1 image: a missing
     * encoder otherwise "succeeds" and writes JPEG bytes into an .avif file.
     */
    public function supportsAvif(): bool
    {
        return (bool) Craft::$app->getCache()->getOrSet('supportsAvif', static function (): bool {
            if (!Craft::$app->getImages()->getIsImagick()) {
                return function_exists('imageavif');
            }

            try {
                $im = new \Imagick();
                $im->newImage(1, 1, new \ImagickPixel('white'));
                $im->setImageFormat('avif');
                $blob = $im->getImageBlob();
                $im->destroy();

                return $blob !== '';
            } catch (\Throwable $e) {
                Craft::info('AVIF encoding unavailable: ' . $e->getMessage(), __METHOD__);

                return false;
            }
        }, self::AVIF_TTL);
    }

    /**
     * The quality to ask for when transforming to AVIF.
     */
    public function avifQuality(): int
    {
        return self::AVIF_QUALITY;
    }
}
