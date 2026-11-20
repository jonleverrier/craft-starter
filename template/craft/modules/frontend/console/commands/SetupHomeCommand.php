<?php

namespace modules\frontend\console\commands;

use CraftCms\Cms\Entry\Data\EntryType;
use CraftCms\Cms\Entry\Elements\Entry;
use CraftCms\Cms\FieldLayout\FieldLayout;
use CraftCms\Cms\FieldLayout\LayoutElements\Entries\EntryTitleField;
use CraftCms\Cms\Section\Data\Section;
use CraftCms\Cms\Section\Data\SectionSiteSettings;
use CraftCms\Cms\Section\Enums\SectionType;
use CraftCms\Cms\Support\Facades\Elements;
use CraftCms\Cms\Support\Facades\EntryTypes;
use CraftCms\Cms\Support\Facades\Sections;
use CraftCms\Cms\Support\Facades\Sites;
use Illuminate\Console\Command;

/**
 * Creates the Home single (URI __home__, template _routers/single). Safe to re-run.
 * Run by tools/setup.sh.
 *
 *     ddev craft frontend:setup:home
 */
class SetupHomeCommand extends Command
{
    protected $signature = 'frontend:setup:home';

    protected $description = 'Create the Home single';

    public function handle(): int
    {
        if (Sections::getSectionByHandle('home')) {
            $this->titleHome();
            $this->line('Home already exists.');
            return self::SUCCESS;
        }

        $entryType = EntryTypes::getEntryTypeByHandle('home') ?? new EntryType([
            'name' => 'Home',
            'handle' => 'home',
        ]);

        if (!$entryType->id) {
            // Craft 6 only gives an entry type a title field when its layout has one.
            $entryType->setFieldLayout(FieldLayout::createFromConfig([
                'type' => Entry::class,
                'tabs' => [
                    [
                        'name' => 'Content',
                        'elements' => [
                            ['type' => EntryTitleField::class],
                        ],
                    ],
                ],
            ]));
        }

        if (!$entryType->id && !EntryTypes::saveEntryType($entryType)) {
            $this->error('Could not save the Home entry type: ' . json_encode($entryType->errors()->toArray()));
            return self::FAILURE;
        }

        $section = new Section([
            'name' => 'Home',
            'handle' => 'home',
            'type' => SectionType::Single,
        ]);
        $section->setSiteSettings([
            new SectionSiteSettings([
                'siteId' => Sites::getPrimarySite()->id,
                'enabledByDefault' => true,
                'hasUrls' => true,
                'uriFormat' => '__home__',
                'template' => '_routers/single',
            ]),
        ]);
        $section->setEntryTypes([$entryType]);

        if (!Sections::saveSection($section)) {
            $this->error('Could not save the Home section: ' . json_encode($section->errors()->toArray()));
            return self::FAILURE;
        }

        $this->titleHome();

        $this->line('Home created.');
        return self::SUCCESS;
    }

    /**
     * Craft 5 titled a new single after its section; Craft 6 (beta) leaves it blank.
     */
    private function titleHome(): void
    {
        $home = Entry::find()->section('home')->status(null)->one();

        if ($home && !$home->title) {
            $home->title = 'Home';
            Elements::saveElement($home);
        }
    }
}
