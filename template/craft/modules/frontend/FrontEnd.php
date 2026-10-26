<?php

namespace modules\frontend;

use Craft;
use craft\web\twig\variables\CraftVariable;
use modules\frontend\variables\FrontEndVariable;
use yii\base\Event;
use yii\base\Module as BaseModule;

/**
 * FrontEnd module — template helpers (craft.frontend) and site setup commands.
 *
 * @method static FrontEnd getInstance()
 */
class FrontEnd extends BaseModule
{
    public function init(): void
    {
        Craft::setAlias('@modules/frontend', __DIR__);

        if (Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'modules\\frontend\\console\\controllers';
        } else {
            $this->controllerNamespace = 'modules\\frontend\\controllers';
        }

        parent::init();

        Craft::$app->onInit(function() {
            $this->attachEventHandlers();

            Event::on(
                CraftVariable::class,
                CraftVariable::EVENT_INIT,
                function (Event $event) {
                    $event->sender->set('frontend', FrontEndVariable::class);
                }
            );
        });
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here.
        // https://craftcms.com/docs/5.x/extend/events.html
    }
}
