<?php

namespace bariew\moduleModule\models;

use app\config\ConfigManager;
use Yii;
use yii\base\Model;
use yii\console\controllers\MigrateController;
use yii\web\Application;

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
        $config =self::getConfig();
        $modules = $config->data['modules'];
        $addMigrations = [];
        $removeMigrations = [];
        foreach ($data as $id => $attributes) {
            $item = self::findOne($id);
            if (!isset($attributes['installed'])) {
                $removeMigrations[] = ['module-down', [$item->moduleName]];
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
            $addMigrations[] = ['module-up', [$attributes['moduleName']]];
        }

        return
            self::migrate($removeMigrations)
            && $config->put(compact('modules'))
            && self::migrate($addMigrations);
    }

    public static function migrate($actions)
    {
        if (!$actions) {
            return true;
        }
        $app = new Application(self::getConfig()->data);
        /**
         * @var MigrateController $controller
         */
        $controller =  $app->createController('migrate')[0];
        $controller->interactive = false;
        defined('STDOUT') or define ('STDOUT', 'php://stdout');
        foreach ($actions as $action) {
            $controller->runAction($action[0], $action[1]);
        }
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
            $composerData = json_decode(file_get_contents(Yii::getAlias($alias . '/composer.json')), true);
            $config = array_merge($config, [
                'id'         => $class,
                'installed'  => isset($modules[$class]),
                'name'       => $name,
                'moduleName' => $moduleName,
                'class'      => $class,
                'basePath'   => $basePath,
                'alias'      => $alias,
                'params'     => isset($modules[$class]) ? $modules[$class]->params : [],
                'description'=> @$composerData['description']
            ]);
            $result[$class] = $config;
        }
        ksort($result);
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

    public static function getModuleByClassName($class)
    {
        $list = self::moduleList();
        return isset($list[$class])
            ? $list[$class]
            : false;
    }

    protected static function getConfig()
    {
        return new \bariew\phptools\FileModel(Yii::getAlias('@app/config/web.php'), [
            'writePath' => Yii::getAlias('@app/config/local/main.php')
        ]);
    }
}
