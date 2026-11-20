<?php

namespace modules\frontend\variables;

use CraftCms\Cms\Image\Images;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Twig\Markup;

/**
 * Template helpers exposed as `craft.frontend`.
 *
 * The Vite helpers sit on Laravel's Vite (the same one behind Craft 6's `vite()` Twig
 * function), adding what nystudio107/craft-vite used to: inlined CSS, critical CSS and a
 * non-blocking stylesheet.
 */
class FrontEndVariable
{
    /** Cookie recording which build's critical CSS the visitor already has. */
    private const CRITICAL_COOKIE = 'criticalcss';

    /** Roughly three months. */
    private const CRITICAL_COOKIE_LIFETIME = 7890000;

    /** Where rollup-plugin-critical writes (criticalBase in vite.config.js), and its file suffix. */
    private const CRITICAL_DIR = 'dist/criticalcss';
    private const CRITICAL_SUFFIX = '_critical.min.css';

    /** A month: AVIF support is a property of the host. */
    private const AVIF_TTL = 2592000;

    /** AVIF quality, separate from the WebP/JPEG quality. */
    private const AVIF_QUALITY = 72;

    /**
     * Is `npm run dev` running? (laravel-vite-plugin writes public/hot while it is.)
     */
    public function viteHot(): bool
    {
        return $this->vite()->isRunningHot();
    }

    /**
     * A built entry's contents, for inlining — e.g. the @font-face rules in fonts.scss.
     */
    public function viteContent(string $entry): Markup
    {
        try {
            $css = $this->vite()->content($entry);
        } catch (\Throwable $e) {
            // Nothing built yet: the page still renders, just without the inlined CSS.
            Log::warning("Vite content unavailable for $entry: " . $e->getMessage());
            $css = '';
        }

        return new Markup($css, 'UTF-8');
    }

    /**
     * An entry's tags with its stylesheets loaded without blocking render: they apply
     * once downloaded, while the inlined critical CSS paints the first view.
     */
    public function viteAsync(string|array $entries): Markup
    {
        // A copy, so the shared Vite instance (and the plain `vite()` tags) keep blocking CSS.
        $vite = clone $this->vite();
        $vite->useStyleTagAttributes(fn () => [
            'media' => 'print',
            'onload' => "this.media='all'",
        ]);

        return new Markup((string) $vite($entries), 'UTF-8');
    }

    /**
     * The critical CSS for a template, inlined. `template` is the view's path, with or
     * without .twig — the name rollup-plugin-critical wrote it under (CRITICAL_PAGES).
     */
    public function criticalCss(?string $template): Markup
    {
        if (!$template || $this->viteHot()) {
            return new Markup('', 'UTF-8');
        }

        $name = preg_replace('/\.twig$/', '', ltrim($template, '/'));
        $file = public_path(self::CRITICAL_DIR . '/' . $name . self::CRITICAL_SUFFIX);

        if (!is_file($file)) {
            return new Markup('', 'UTF-8');
        }

        return new Markup('<style>' . file_get_contents($file) . '</style>', 'UTF-8');
    }

    /**
     * Has this visitor already been served the current build's critical CSS?
     *
     * A miss also SETS the cookie, so the next page load is a hit. The cookie holds the
     * build's manifest hash, so a new build invalidates it on its own.
     */
    public function hasCriticalCssCookie(): bool
    {
        // Empty when nothing is built: then critical CSS is always inlined.
        $hash = (string) ($this->vite()->manifestHash() ?? '');

        if (($_COOKIE[self::CRITICAL_COOKIE] ?? null) === $hash) {
            return true;
        }

        // Raw, not through Laravel's encrypted cookies, so JS or an edge cache can read it.
        setcookie(self::CRITICAL_COOKIE, $hash, [
            'expires' => time() + self::CRITICAL_COOKIE_LIFETIME,
            'path' => '/',
            // Secure cookies are dropped on plain http.
            'secure' => request()->isSecure(),
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
        return (bool) Cache::remember('supportsAvif', self::AVIF_TTL, static function (): bool {
            if (!app(Images::class)->getIsImagick()) {
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
                Log::info('AVIF encoding unavailable: ' . $e->getMessage());

                return false;
            }
        });
    }

    /**
     * The quality to ask for when transforming to AVIF.
     */
    public function avifQuality(): int
    {
        return self::AVIF_QUALITY;
    }

    private function vite(): Vite
    {
        return app(Vite::class);
    }
}
