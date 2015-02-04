<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 19.01.15
 * Time: 10:28
 */

namespace bariew\moduleModule\models;


use bariew\moduleModule\models\HtmlOutput;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use yii\data\ArrayDataProvider;
use Yii;

class Composer extends Item
{
    const ACTION_INSTALL = 0;
    const ACTION_UPDATE = 1;
    const ACTION_REMOVE = 2;
    const ACTION_SKIP = 3;

    public static function actionList()
    {
        return [
            self::ACTION_INSTALL => Yii::t('modules/module', 'Install'),
            self::ACTION_UPDATE => Yii::t('modules/module', 'Update'),
            self::ACTION_REMOVE => Yii::t('modules/module', 'Remove'),
            self::ACTION_SKIP => Yii::t('modules/module', 'Skip'),
        ];
    }

    public function getDefaultAction()
    {
        return isset(Yii::$app->extensions[$this->name]) ? self::ACTION_SKIP : null;
    }

    public function search($params)
    {
        $query = "https://packagist.org/search.json?tags[]=yii2&tags[]=module";
        if (isset($params['page'])) {
            $query .= '&page='.$params['page'];
        }
        $this->load($params);
        if ($this->name) {
            $query .= "&q=".$this->name;
        }
        $response = json_decode(file_get_contents($query), true);
        $items = $response["results"];
        foreach ($items as $key => $attributes) {
            $items[$key] = new self(compact('attributes'));
        }
        $dataProvider = new ArrayDataProvider(['allModels' => $items, 'key' => function ($model) {return $model['name']; }]);
        $dataProvider->setModels($items);
        $dataProvider->pagination->pageSize = 15;
        $dataProvider->pagination->totalCount = $response['total'];
        return $dataProvider;
    }


    public static function requireAll($names)
    {
        if (!$names) {
            return true;
        }
        foreach ($names as $key => &$name) {
            $name .= ":dev-master";
        }
        return self::runComposer([
            'command' => 'require',
            'packages' => $names,
            '--no-interaction' => true,
            '--prefer-dist' => true,
        ]);
    }

    public static function installAll()
    {
        return self::runComposer([
            'command' => 'install',
            '--no-interaction' => true,
            '--prefer-dist' => true,
            '' => true,
        ]);
    }

    public static function updateAll($names)
    {
        if (!$names) {
            return true;
        }
        return self::runComposer([
            'command' => 'update',
            'packages' => $names,
            '--no-interaction' => true,
        ]);
    }

    public static function removeAll($names)
    {
        if (!$names) {
            return true;
        }
        return self::runComposer([
            'command' => 'remove',
            'packages' => $names,
            '--no-interaction' => true,
        ]);
    }

    public static function runComposer(array $command, $render = true)
    {
        chdir(Yii::$app->basePath);
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        foreach (['xdebug_stop_code_coverage', 'xdebug_disable'] as $function) {
            if (function_exists($function)) {
                $function();
            }
        }
        $output = $render ? self::htmlOutput() : null;

        return (new Application())->run(new ArrayInput($command), $output);
    }

    public static function htmlOutput()
    {
        $output = new HtmlOutput(fopen('php://stdout', 'w'));
        register_shutdown_function(function () use ($output) {
            foreach ($output->messages as $key => $message) {
                if (preg_match('/Exception/', $message)) {
                    Yii::$app->session->setFlash('error', $output->messages[$key+1]);
                } elseif (preg_match('/Problem.*/', $message)) {
                    Yii::$app->session->setFlash('error', $message);
                }
            }
            if (!Yii::$app->session->hasFlash('error')) {
                Yii::$app->session->setFlash('success', Yii::t('modules/module', 'Success!'));
            }
            echo Yii::$app->controller->actionIndex(false);
        });
        return $output;
    }
} 