<?php

namespace modules\frontend;

use CraftCms\Cms\Image\Images as CraftImages;
use CraftCms\Cms\Twig\Variables\CraftVariable;
use Illuminate\Support\ServiceProvider;
use modules\frontend\console\commands\SetupAssetsCommand;
use modules\frontend\console\commands\SetupHomeCommand;
use modules\frontend\services\Images;
use modules\frontend\variables\FrontEndVariable;

/**
 * Template helpers (craft.frontend), the image service's AVIF options, and the site
 * setup commands.
 */
class FrontEndServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Craft's image service plus two AVIF encoder options (see the class).
        $this->app->singleton(CraftImages::class, Images::class);
    }

    public function boot(): void
    {
        CraftVariable::macro('frontend', fn () => app(FrontEndVariable::class));

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupHomeCommand::class,
                SetupAssetsCommand::class,
            ]);
        }

        // Register event listeners here.
        // https://craftcms.com/docs/6.x/extend/events.html
    }
}
