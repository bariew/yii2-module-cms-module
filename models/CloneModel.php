<?php
/**
 * CloneModel class file.
 * @copyright (c) 2014, bariew
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace bariew\moduleModule\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

/**
 * This model is for cloning modules.
 * E.g. you have module installed in /vendor folder
 * and you want to extend its behavior with new module - 
 * you can just clone it into @app/modules/yourNewModule folder.
 * All classes in your new module will be empty classes extending original ones 
 * (you may place there your new content or not - it already works).
 * 
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class CloneModel extends Model
{
    /**
     * @var string yii2 path alias to original module folder.
     */
    public $source;
    
    /**
     * @var string yii2 path alias to target module folder.
     */
    public $destination;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source', 'destination'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'source'  => Yii::t('modules/module', 'Clone source'),
            'destination'       => Yii::t('modules/module', 'Clone destination'),
        ];
    }
    
    /**
     * Gets module cloning done.
     * @return boolean 
     */
    public function run()
    {
        return $this->copy() && $this->clear() ;
        
    }
    
    /**
     * Searches all path aliases.
     * @param string $query search string started with @
     * @return array found system path aliases matching search query.
     */
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
    
    /**
     * Copies orihinal module files to destination folder.
     * @return boolean
     */
    private function copy()
    {
        $source = Yii::getAlias($this->source);
        if (!file_exists($source) || !is_dir($source)) {
            $this->addError('source', Yii::t('modules/module', 'Source directory not found'));
            return false;
        }
        $destination = Yii::getAlias($this->destination);
        if (file_exists($destination)) {
            $this->addError('destination', Yii::t('modules/module', 'Destination directory already exists'));
            return false;
        }
        FileHelper::copyDirectory($source, $destination);
        return true;
    }
    
    /**
     * Relaces all new module classes content with empty template.
     * @return boolean
     */
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
        return true;
    }
}
