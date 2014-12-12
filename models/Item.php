<?php

namespace bariew\moduleModule\models;

use app\config\ConfigManager;
use bariew\moduleMigration\ModuleMigration;
use bariew\moduleModule\Module;
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

    public function rules()
    {
        return [
            [['moduleName'], 'required'],
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
        $items = [];
        $errors = [];
        if (!$data) {
            return false;
        }
        foreach ($data as $id => $attributes) {
            $items[] = $item = self::findOne($id);
            $item->installed = @$attributes['installed'];
            $item->attributes = $attributes;
            if (!$item->installed) {
                $item->uninstall();
            } else if(!$item->validate()) {
                $errors[] = $item;
            } else {
                $item->install();
            }
        }
        return $errors ? $items : true;
    }


    public function install()
    {
        $config = self::getConfig();
        $modules = $config->data['modules'];
        if ($module = self::getModuleByClassName($this->class)) {
            $modules[$this->moduleName]['params'] = $module->params;
            unset($modules[$module->id]);
        }
        $modules[$this->moduleName] = [
            'class' => $this->class
        ];
        $config->put(compact('modules'));
        Yii::configure(Yii::$app, compact('modules'));
        self::migrate([['module-up', [$this->moduleName]]]);
    }

    public function uninstall()
    {
        $config = self::getConfig();
        $modules = $config->data['modules'];
        $bootstrap = $config->data['bootstrap'];
        unset($modules[$this->moduleName]);
        $bootstrap = array_diff($bootstrap, [$this->moduleName]);
        $config->put(compact('modules', 'bootstrap'));
        self::migrate([['module-down', [$this->moduleName]]]);
    }

    public static function migrate($actions)
    {
        if (!$actions) {
            return true;
        }
        /**
         * @var MigrateController $controller
         */
        $controller = new ModuleMigration('migrate', self::getModuleByClassName(Module::className()));
        $controller->interactive = false;
        ob_start();
        defined('STDOUT') or define ('STDOUT', 'php://stdout');
        foreach ($actions as $action) {
            $controller->runAction($action[0], $action[1]);
        }
        ob_clean();
        Yii::$app->cache->flush();
        return true;
    }

    public static function extensionList()
    {
        $result = [];
        $modules = self::moduleList();
        $knownModules = ['yiisoft/yii2-gii', 'yiisoft/yii2-debug'];
        $keywords = ['yii2', 'module'];

        foreach (Yii::$app->extensions as $name => $config) {
            $alias = key($config['alias']);
            $composerData = json_decode(file_get_contents(Yii::getAlias($alias . '/composer.json')), true);
            if (!in_array($name, $knownModules) && (!isset($composerData['keywords']) || array_diff($keywords, $composerData['keywords']))) {
                continue;
            }
            $basePath = $config['alias'][$alias];
            $class = str_replace(['@', '/'], ['', '\\'], $alias) .'\Module';
            $moduleName = isset($modules[$class]) ? $modules[$class]->id : $class;
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
        return $result;
    }

    public static function moduleList()
    {
        $modules = [];
        foreach(Yii::$app->modules as $id => $options) {
            $module = Yii::$app->getModule($id);
            $modules[get_class($module)] = $module;
        }
        return $modules;
    }

    /**
     * @param $class
     * @return \yii\base\Module
     */
    public static function getModuleByClassName($class)
    {
        $list = self::moduleList();
        return isset($list[$class])
            ? $list[$class]
            : false;
    }

    private static $_config;
    protected static function getConfig()
    {
        return self::$_config
            ? self::$_config
            : self::$_config = new \bariew\phptools\FileModel(Yii::getAlias('@app/config/web.php'), [
                'writePath' => Yii::getAlias('@app/config/local/main.php')
            ]);
    }

}
