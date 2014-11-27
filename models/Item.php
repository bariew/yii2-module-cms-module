<?php

namespace bariew\moduleModule\models;

use app\config\ConfigManager;
use Yii;
use yii\base\Model;
use yii\console\Application as ConsoleApplication;
use yii\console\controllers\MigrateController;

class Item extends Model
{
    public $name;
    public $description;
    public $downloads;
    public $url;
    public $favers;
    public $repository;
    public $id;
    public $class;
    public $basePath;
    public $alias;
    public $moduleName;
    public $version;
    public $bootstrap;
    public $installed;
    public $params;

    private static $_extensionList;
    private static $_moduleList;

    public function rules()
    {
        return [
            [['id', 'name', 'url', 'repository', 'description', 'version', 'moduleName', 'class', 'basePath', 'alias'], 'string'],
            [['downloads', 'favers', 'installed'], 'integer'],
            [['params'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'moduleName'  => Yii::t('modules/module', 'Module name'),
            'class'       => Yii::t('modules/module', 'Class'),
            'basePath'    => Yii::t('modules/module', 'Base path'),
            'alias'       => Yii::t('modules/module', 'Alias'),
            'name'        => Yii::t('modules/module', 'Name'),
            'description' => Yii::t('modules/module', 'Description'),
            'downloads'   => Yii::t('modules/module', 'Downloads'),
            'url'         => Yii::t('modules/module', 'Url'),
            'favers'      => Yii::t('modules/module', 'Favers'),
            'repository'  => Yii::t('modules/module', 'Repository'),
        ];
    }

    public static function findAll()
    {
        $items = [];
        foreach (self::extensionList() as $params) {
            $model = new self(['attributes' => $params]);
            $items[] = $model;
        }
        return $items;
    }

    /**
     * @param $id
     * @return Item
     */
    public static function findOne($id)
    {
        return new self(self::extensionList()[$id]);
    }

    public static function updateAll($data)
    {
        if (!$data) {
            return false;
        }
        $config = new ConfigManager();
        $modules = $config->mainConfig['modules'];
        foreach ($data as $id => $attributes) {
            $item = self::findOne($id);
            if (!isset($attributes['installed'])) {
                unset($modules[$item->moduleName]);
                continue;
            }
            $modules[$attributes['moduleName']] = [
                'class' => $item->class
            ];
            if (isset(self::moduleList()[$item->class])) {
                $modules[$attributes['moduleName']]['params']
                    = self::moduleList()[$item->class]->params;
            }
        }
        return $config->put(compact('modules'));
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

    public static function extensionList()
    {
        if (self::$_extensionList) {
            return self::$_extensionList;
        }
        $result = [];
        $modules = self::moduleList();
        foreach (Yii::$app->extensions as $name => $config) {
            $extName = preg_replace('/.*\/(.*)$/', '$1', $name);
            if(!preg_match('/yii2-(.+)-cms-module/', $extName, $matches)){
                continue;
            }
            $alias = key($config['alias']);
            $basePath = $config['alias'][$alias];
            $class = str_replace(['@', '/'], ['', '\\'], $alias) .'\Module';
            $moduleName = isset($modules[$class]) ? $modules[$class]->id : $matches[1];
            $config = array_merge($config, [
                'id'         => $class,
                'installed'  => isset($modules[$class]),
                'name'       => $name,
                'moduleName' => $moduleName,
                'class'      => $class,
                'basePath'   => $basePath,
                'alias'      => $alias,
                'params'     => isset($modules[$class]) ? $modules[$class]->params : [],
            ]);
            $result[$class] = $config;
        }
        return self::$_extensionList = $result;
    }

    public static function moduleList()
    {
        if (self::$_moduleList) {
            return self::$_moduleList;
        }
        $modules = [];
        foreach(Yii::$app->modules as $id => $options) {
            $module = Yii::$app->getModule($id);
            $modules[get_class($module)] = $module;
        }
        return self::$_moduleList = $modules;
    }
}
