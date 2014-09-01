<?php

namespace bariew\moduleModule\models;

use bariew\moduleModule\HtmlOutput;
use bariew\moduleModule\ModuleBootstrap;
use Codeception\Platform\SimpleOutput;
use SebastianBergmann\Exporter\Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use Composer\Console\Application;
use yii\console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use yii\console\controllers\MigrateController;
use yii\helpers\BaseFileHelper;

class Item extends Model
{
    public $name;
    public $description;
    public $downloads;
    public $url;
    public $favers;
    public $repository;

    public $version;
    public $alias;
    public $bootstrap;

    public static $extConfigFile = '@vendor/yiisoft/extensions.php';

    public static $migrationCommands = [];

    public function rules()
    {
        return [
            [['name', 'url', 'repository', 'description', 'version'], 'string'],
            [['downloads', 'favers'], 'integer'],
            [['name'], 'moduleNameValidation']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name'    => Yii::t('modules/module', 'Name'),
            'description'    => Yii::t('modules/module', 'Description'),
            'downloads'    => Yii::t('modules/module', 'Downloads'),
            'url'    => Yii::t('modules/module', 'Url'),
            'favers'    => Yii::t('modules/module', 'Favers'),
            'repository'    => Yii::t('modules/module', 'Repository'),
        ];
    }

    public function moduleNameValidation($attribute)
    {
        if (!self::getModuleName($this->$attribute)) {
            $this->addError($attribute, "Not CMS module");
        }
    }

    public static function composerConfig()
    {
        return json_decode(file_get_contents(
            Yii::$app->basePath . DIRECTORY_SEPARATOR . 'composer.json'
        ), true);
    }

    public static function installedList()
    {
        return Yii::$app->extensions;
    }

    public function getIsInstalled()
    {
        return isset(self::installedList()[$this->name]);
    }

    public static function getModuleName($name)
    {
        return  (preg_match('/yii2-(.*)-cms-module/', $name, $matches))
            ? $matches[1] : null;
    }

    /**
     * @return null|\yii\base\Module
     */
    public function getModule()
    {
        return Yii::$app->getModule(self::getModuleName($this->name));
    }


    public function hasLocalParams()
    {
        return file_exists($this->getModule()->basePath . DIRECTORY_SEPARATOR . 'params-local.php');
    }

    public function search($params)
    {
        $query = "https://packagist.org/search.json?tags[]=yii2-null-cms-module";

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

    public static function findAll()
    {
        $items = [];
        foreach (self::installedList() as $options) {
            $model = new self(['attributes' => $options]);
            if (!$model->validate()) {
                continue;
            }
            $items[] = $model;
        }
        return new ArrayDataProvider(['allModels' => $items, 'key' => function ($model) {return $model['name']; }]);
    }


    public static function install($names)
    {
        if (!$names) {
            return true;
        }
        foreach ($names as $key => &$name) {
            if (!($moduleName = self::getModuleName($name)) || (!$module = Yii::$app->getModule($moduleName))) {
                unset($names[$key]);
                continue;
            }
            $name .= ":dev-master";
            self::$migrationCommands[] = ['module-up', [$moduleName]];
        }
        return self::runComposer([
            'command' => 'require',
            'packages' => $names
        ]);
    }

    public static function update($names)
    {
        if (!$names) {
            return true;
        }
        foreach ($names as $key => &$name) {
            if (!($moduleName = self::getModuleName($name)) || (!$module = Yii::$app->getModule($moduleName))) {
                unset($names[$key]);
                continue;
            }
        }
        return self::runComposer([
            'command' => 'update',
            'packages' => $names
        ]);
    }

    public static function remove($names)
    {
        if (!$names) {
            return true;
        }
        foreach ($names as $name) {
            if (!$moduleName = self::getModuleName($name)) {
                continue;
            }
            if(!$module = Yii::$app->getModule($moduleName)) {
                continue;
            }
            if (method_exists($module, 'uninstall')) {
                call_user_func([$module, 'uninstall']);
            }
            self::$migrationCommands[] = ['module-down', [$moduleName]];
        }
        self::migrate(self::$migrationCommands);
        return self::runComposer([
            'command' => 'remove',
            'packages' => $names
        ]);
    }


    protected static function checkRequirements()
    {
        $s = DIRECTORY_SEPARATOR;
        $root = Yii::$app->basePath;
        $composerSettings = self::composerConfig();
        foreach ($composerSettings['extra']['writable'] as $path) {
            $fullPath = $root . $s . $path;
            $fullPath = is_file($fullPath) ? $fullPath : $fullPath.$s;
            if (!is_writable($fullPath)) {
                throw new InvalidConfigException("{$path} is not writable");
            }
        }
    }

    public static function runComposer(array $command, $render = true)
    {
        self::checkRequirements();
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
            $success = true;
            foreach ($output->messages as $key => $message) {
                if (preg_match('/Exception/', $message)) {
                    $success = false;
                    Yii::$app->session->setFlash('error', $output->messages[$key+1]);
                    break;
                } elseif (preg_match('/Problem.*/', $message)) {
                    $success = false;
                    Yii::$app->session->setFlash('error', $message);
                    break;
                }
                Yii::$app->session->setFlash('info', $message);
            }
            Yii::$app->extensions = require Yii::getAlias(self::$extConfigFile);
            $bootstrap = new ModuleBootstrap();
            $bootstrap->app = Yii::$app;
            $bootstrap->attachModules();
            if ($success) {
                self::migrate(self::$migrationCommands);
                Yii::$app->session->setFlash('success', Yii::t('modules/user', "Ready."));
            }
            echo Yii::$app->controller->actionIndex();
        });
        return $output;
    }

    public static function migrate(&$actions)
    {
        if (!$actions) {
            return true;
        }
        $webApp = Yii::$app;
        try {
            $consoleConfig = require_once Yii::getAlias('@app/config/console.php');
            Yii::$app = new ConsoleApplication($consoleConfig);
            /**
             * @var MigrateController $controller
             */
            $controller =  Yii::$app->createController('migrate')[0];
            $controller->interactive = false;
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            defined('YII_DEBUG') or define('YII_DEBUG', true);
            defined('YII_ENV') or define('YII_ENV', 'dev');
            defined('STDOUT') or define ('STDOUT', 'php://stdout');
            foreach ($actions as $action) {
                $controller->runAction($action[0], $action[1]);
            }
            Yii::$app->response->clearOutputBuffers();
        } catch (\Exception $e) {
            Yii::$app = $webApp;
            echo $e->getMessage() . "\n\n" . $e->getTraceAsString();
            Yii::$app->session->setFlash('error', $e->getMessage());
            return false;
        }
        $actions = [];
        Yii::$app = $webApp;
        return true;
    }
}
