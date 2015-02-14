<?php

namespace bariew\moduleModule\models;

use app\config\ConfigManager;
use bariew\moduleMigration\ModuleMigration;
use bariew\moduleModule\Module;
use Yii;
use yii\base\Model;
use yii\console\controllers\MigrateController;
use yii\helpers\FileHelper;

class CloneModel extends Model
{
    public $source;
    public $destination;

    public function rules()
    {
        return [
            [['source', 'destination'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'source'  => Yii::t('modules/module', 'Clone source'),
            'destination'       => Yii::t('modules/module', 'Clone destination'),
        ];
    }
    
    public static function findAliases($query)
    {
        $result = [];
        foreach (\Yii::$aliases as $alias => $data) {
            if (strpos($alias, $query) === 0) {
                $result[] = $alias . '/'; // e.g query '@bar' matches alias '@bariew'
                continue;
            } else if (strpos($query, $alias) !== 0) {
                continue;
            } else if (($query == $alias . '/') && is_array($data)) {
                return array_keys($data); // e.g. query @bariew/ matches '@bariew/*' alias children
            } else if (!is_array($data)) {
                $result[] = $alias . '/'; 
                continue; 
            }
            foreach (array_keys($data) as $alias) {
                if (strpos($alias, $query) === 0) {
                    $result[] = $alias;
                }
            }       
        }
        return $result;
    }
    
    public function run()
    {
        return $this->copy()
                ->clear() ;
        
    }
    
    private function copy()
    {
        $source = Yii::getAlias($this->source);
        if (!file_exists($source) || !is_dir($source)) {
            $this->addError('source', Yii::t('modules/module', 'Source directory not found'));
            return false;
        }
        $destination = Yii::getAlias($this->destination);
//        if (file_exists($destination) && FileHelper::findFiles($destination)) {
//            $this->addError('destination', Yii::t('modules/module', 'Destination directory is not empty'));
//            return false;
//        }
        FileHelper::copyDirectory($source, $destination);
        return $this;
    }
    
    private function clear()
    {
        $destination = Yii::getAlias($this->destination);
        $newNamespace = str_replace(['@', '/'], ['', '\\'], $this->destination);
        $oldNamespace = str_replace(['@', '/'], ['', '\\'], $this->source);
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'clone_template');
        foreach (FileHelper::findFiles($destination) as $path) {
            $content = str_replace($oldNamespace, $newNamespace, file_get_contents($path));
            file_put_contents($path, $content);
            if (!preg_match('/^.*\W([A-Z]\w+)\.php/', $path, $matches)) {
                continue;
            }
            $className = $matches[1];
            $addedNamespace = str_replace(
                [$destination, DIRECTORY_SEPARATOR . $className .'.php', '/'], 
                ['', '', '\\'], 
                $path
            );
            $fileNamespace = $newNamespace . $addedNamespace; 
            $oldClassName = $oldNamespace . $addedNamespace . '\\' . $className;
            $classContent = str_replace(
                ['$namespace', '$class', '$oldclass'], 
                [$fileNamespace, $className, $oldClassName], 
                $template
            );
            file_put_contents($path, $classContent);
        }
    }
}
