<?php

namespace modules\frontend\console\commands;

use CraftCms\Cms\Asset\Data\Volume;
use CraftCms\Cms\Asset\Elements\Asset;
use CraftCms\Cms\FieldLayout\FieldLayout;
use CraftCms\Cms\FieldLayout\LayoutElements\Assets\AltField;
use CraftCms\Cms\FieldLayout\LayoutElements\Assets\AssetTitleField;
use CraftCms\Cms\Support\Facades\Volumes;
use Illuminate\Console\Command;

/**
 * Creates the Local volume (title and alt text) on the `assets` disk, public/assets
 * (config/filesystems.php). Safe to re-run. Run by tools/setup.sh.
 *
 *     ddev craft frontend:setup:assets
 */
class SetupAssetsCommand extends Command
{
    protected $signature = 'frontend:setup:assets';

    protected $description = 'Create the Local volume';

    public function handle(): int
    {
        if (Volumes::getVolumeByHandle('local')) {
            $this->line('Local volume already exists.');
            return self::SUCCESS;
        }

        $volume = new Volume([
            'name' => 'Local',
            'handle' => 'local',
            'fsHandle' => 'assets',
            'hasUrls' => true,
        ]);
        $volume->setFieldLayout(FieldLayout::createFromConfig([
            'type' => Asset::class,
            'tabs' => [
                [
                    'name' => 'Content',
                    'elements' => [
                        ['type' => AssetTitleField::class],
                        ['type' => AltField::class],
                    ],
                ],
            ],
        ]));

        if (!Volumes::saveVolume($volume)) {
            $this->error('Could not save the Local volume: ' . json_encode($volume->errors()->toArray()));
            return self::FAILURE;
        }

        $this->line('Local volume created.');
        return self::SUCCESS;
    }
}
