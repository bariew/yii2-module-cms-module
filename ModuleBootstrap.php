<?php
/**
 * ModuleBootstrap class file
 * @copyright Copyright (c) 2014 Galament
 * @license http://www.yiiframework.com/license/
 */

namespace bariew\moduleModule;

use bariew\moduleModule\models\Menu;
use bariew\moduleModule\widgets\MenuWidget;
use Yii;
use yii\base\BootstrapInterface;
use yii\composer\Installer;
use \yii\web\Application;
use yii\base\Event;
use yii\bootstrap\NavBar;
use yii\web\View;

/**
 * Bootstrap class initiates external modules.
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class ModuleBootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->controllerMap['migrate'] = 'bariew\moduleMigration\ModuleMigration';
        if ($app instanceof Application) {
            $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
                $app->getView()->on(View::EVENT_BEGIN_BODY, [$this, 'renderMenu']);
            });
        }
        return true;
    }

    public function renderMenu()
    {
        if (Yii::$app->getRequest()->getIsAjax() || !Yii::$app->getModule('module')->params['renderMenu']) {
            return;
        }

        NavBar::begin([
            'brandLabel' => 'Home',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo MenuWidget::widget([
            'options' => ['class' => 'navbar-nav navbar-right']
        ]);

        NavBar::end();
    }
}