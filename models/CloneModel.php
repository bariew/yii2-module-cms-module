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
     * @var int whether to replace existing files while cloning.
     */
    public $replace = 0;

    /**
     * @var array files not to change.
     */
    private $keepFiles = [];

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
            ['source', 'in', 'range' => self::aliasList()],
            ['replace', 'in', 'range' => array_keys(self::replaceList())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'source'  => Yii::t('modules/module', 'Clone source'),
            'destination'   => Yii::t('modules/module', 'Clone destination'),
            'replace'       => Yii::t('modules/module', 'Replace existing files'),
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
     * Available values for 'replace' attribute.
     * @return array
     */
    public static function replaceList()
    {
        return [
            0 => Yii::t('modules/module', 'No'),
            1 => Yii::t('modules/module', 'Yes'),
        ];
    }

    /**
     * System path aliases list.
     * @return array available alias list.
     */
    public static function aliasList()
    {
        $result = [];
        foreach (\Yii::$aliases as $alias => $data) {
            if (is_array($data)) {
                $result = array_merge($result, array_keys($data));
            } else {
                $result[] = $alias;
            }
        }
        asort($result);
        return array_values($result);
    }

    /**
     * Searches all path aliases.
     * @param string $query search string started with @
     * @return array found system path aliases matching search query.
     */
    public static function findAliases($query)
    {
        $query = '@' . str_replace('@', '', $query);
        $pattern = '/' . preg_quote($query, '/') . '/';
        return preg_grep($pattern, self::aliasList());
    }
    
    /**
     * Copies original module files to destination folder.
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
        FileHelper::copyDirectory($source, $destination, [
            'except' => ['.git/'],
            'fileMode' => 0775,
            'beforeCopy' => function ($from, $to) {
                if ($this->replace || !file_exists($to) || !is_file($to)) {
                    return true;
                }
                $this->keepFiles[] = $to;
                return false;
            }
        ]);
        return true;
    }

    /**
     * Replaces all new module classes content with empty template.
     * @return boolean
     */
    private function clear()
    {
        $destination = Yii::getAlias($this->destination);
        $newNamespace = str_replace(['@', '/'], ['', '\\'], $this->destination);
        $oldNamespace = str_replace(['@', '/'], ['', '\\'], $this->source);
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'clone_template');
        foreach (FileHelper::findFiles($destination) as $path) {
            if (!$this->replace && in_array($path, $this->keepFiles)) {
                continue;
            }
            $content = str_replace($oldNamespace, $newNamespace, file_get_contents($path));
            file_put_contents($path, $content);
            if (!preg_match('/^.*\W([A-Z]\w+)\.php/', $path, $matches)) { // Class file.
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
            file_put_contents($path, "<?php " . $classContent);
        }
        return true;
    }
}
