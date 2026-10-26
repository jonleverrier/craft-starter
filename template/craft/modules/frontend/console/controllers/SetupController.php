<?php

namespace modules\frontend\console\controllers;

use Craft;
use craft\console\Controller;
use craft\elements\Asset;
use craft\fieldlayoutelements\assets\AltField;
use craft\fieldlayoutelements\assets\AssetTitleField;
use craft\fs\Local;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\models\Volume;
use yii\console\ExitCode;

/**
 * One-off site setup, run by tools/setup.sh.
 */
class SetupController extends Controller
{
    /**
     * Creates the Home single (URI __home__, template _routers/single). Safe to re-run.
     *
     *     ddev craft frontend/setup/home
     */
    public function actionHome(): int
    {
        $entries = Craft::$app->getEntries();

        if ($entries->getSectionByHandle('home')) {
            $this->stdout("Home already exists.\n");
            return ExitCode::OK;
        }

        $entryType = $entries->getEntryTypeByHandle('home') ?? new EntryType([
            'name' => 'Home',
            'handle' => 'home',
        ]);

        if (!$entryType->id && !$entries->saveEntryType($entryType)) {
            $this->stderr('Could not save the Home entry type: ' . json_encode($entryType->getErrors()) . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $section = new Section([
            'name' => 'Home',
            'handle' => 'home',
            'type' => Section::TYPE_SINGLE,
            'siteSettings' => [
                new Section_SiteSettings([
                    'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
                    'enabledByDefault' => true,
                    'hasUrls' => true,
                    'uriFormat' => '__home__',
                    'template' => '_routers/single',
                ]),
            ],
        ]);
        $section->setEntryTypes([$entryType]);

        if (!$entries->saveSection($section)) {
            $this->stderr('Could not save the Home section: ' . json_encode($section->getErrors()) . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Home created.\n");
        return ExitCode::OK;
    }

    /**
     * Creates the Local filesystem (public/assets) and the Local volume (title and alt
     * text). Safe to re-run.
     *
     *     ddev craft frontend/setup/assets
     */
    public function actionAssets(): int
    {
        $filesystems = Craft::$app->getFs();
        $volumes = Craft::$app->getVolumes();

        if (!$filesystems->getFilesystemByHandle('local')) {
            // Literal $VARs: Craft stores the env var names, so the path and URL stay in .env.
            $fs = $filesystems->createFilesystem([
                'type' => Local::class,
                'name' => 'Local',
                'handle' => 'local',
                'hasUrls' => true,
                'url' => '$PRIMARY_SITE_FILESYSTEM_URL',
                'settings' => ['path' => '$PRIMARY_SITE_FILESYSTEM_WEBROOT'],
            ]);

            if (!$filesystems->saveFilesystem($fs)) {
                $this->stderr('Could not save the Local filesystem: ' . json_encode($fs->getErrors()) . "\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $this->stdout("Local filesystem created.\n");
        } else {
            $this->stdout("Local filesystem already exists.\n");
        }

        if ($volumes->getVolumeByHandle('local')) {
            $this->stdout("Local volume already exists.\n");
            return ExitCode::OK;
        }

        $volume = new Volume([
            'name' => 'Local',
            'handle' => 'local',
            'fsHandle' => 'local',
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

        if (!$volumes->saveVolume($volume)) {
            $this->stderr('Could not save the Local volume: ' . json_encode($volume->getErrors()) . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Local volume created.\n");
        return ExitCode::OK;
    }
}
