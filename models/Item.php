<?php

namespace bariew\moduleModule\models;

use app\config\ConfigManager;
use bariew\moduleMigration\ModuleMigrateController;
use bariew\moduleModule\Module;
use Yii;
use yii\base\Model;
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
        $config = ['class' => $this->class];
        $modules = Yii::$app->modules;
        if ($module = self::getModuleByClassName($this->class)) {
            $moduleInstalled = true;
            if ($this->moduleName == $module->id) {
                return true;
            }
            $config['params'] = $module->params;
            if (method_exists($module, 'uninstall')) {
                $module->uninstall($module->id);
            }
            unset($modules[$module->id]);
            ConfigManager::remove(['modules', $module->id], $config);
        } else {
            $moduleInstalled = false;
        }
        ConfigManager::set(['modules', $this->moduleName], $config);
        $modules[$this->moduleName] = $config;
        Yii::configure(Yii::$app, compact('modules'));
        if (!$moduleInstalled) {
            self::migrate([['module-up', [$this->moduleName]]]);
            $module = self::getModuleByClassName($this->class);
            if (method_exists($module, 'install')) {
                $module->install($this->moduleName);
            }
        }
    }

    public function uninstall()
    {
        if (!$module = self::getModuleByClassName($this->class)) {
            return true;
        }
        if (method_exists($module, 'uninstall')) {
            $module->uninstall($this->moduleName);
        }
        $config = ConfigManager::getWriteData();
        unset($config['modules'][$this->moduleName]);
        $config['bootstrap'] = array_diff($config['bootstrap'], [$this->moduleName]);
        ConfigManager::remove(['modules', $this->moduleName]);
        ConfigManager::put(['bootstrap' => array_unique($config['bootstrap'])]);

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
        $controller = new ModuleMigrateController('migrate', self::getModuleByClassName(Module::className()));
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
            if (!file_exists(Yii::getAlias($alias . '/composer.json'))) {
                continue;
            }
            $composerData = json_decode(file_get_contents(Yii::getAlias($alias . '/composer.json')), true);
            if (!in_array($name, $knownModules) && (!isset($composerData['keywords']) || array_diff($keywords, $composerData['keywords']))) {
                continue;
            }
            $basePath = $config['alias'][$alias];
            $class = str_replace(['@', '/'], ['', '\\'], $alias) .'\Module';
            $pregModuleName = preg_replace('/^\w+\\\\([a-z0-9]+).*\\\\.*/', '$1', $class);
            $moduleName = isset($modules[$class])
                ? $modules[$class]->id
                : ($pregModuleName
                    ? $pregModuleName
                    : $class);
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
        $result = array_merge($result, self::localModuleList());
        ksort($result);
        return $result;
    }
    
    public static function localModuleList()
    {
        $result = [];
        $dir = Yii::getAlias('@app/modules');
        if (!file_exists($dir)) {
            return $result;
        }
        $modules = self::moduleList();
        $localModules = array_diff(scandir($dir), ['.', '..']);
        foreach ($localModules as $moduleName) {
            $basePath = $dir . DIRECTORY_SEPARATOR . $moduleName;
            if (!is_dir($basePath)) {
                continue;
            }
            $moduleName = basename($basePath);
            $class = "app\\modules\\{$moduleName}\\Module"; 
            $result[$class] = [
                'id'         => $class,
                'installed'  => isset($modules[$class]),
                'name'       => $moduleName,
                'moduleName' => $moduleName,
                'class'      => $class,
                'basePath'   => $basePath,
                'alias'      => "@app/modules/{$moduleName}",
                'params'     => isset($modules[$class]) ? $modules[$class]->params : [],
                'description'=> ""                
            ];
        }
        return $result;
    }

    public static function moduleList()
    {
        $modules = [];
        foreach(Yii::$app->modules as $id => $options) {
            try {
                $module = Yii::$app->getModule($id);
            } catch (\Exception $e) {
                continue;
            }

            $modules[get_class($module)] = $module;
        }
        return $modules;
    }

    public function isInstalled()
    {
        return self::getModuleByClassName($this->class);
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
}
