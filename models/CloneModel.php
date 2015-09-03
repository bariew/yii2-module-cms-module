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

    public $inheritContent = 0;

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
            [['inheritContent', 'replace'], 'boolean'],
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
            'inheritContent'=> Yii::t('modules/module', 'Inherit parent php content'),
            'replace'       => Yii::t('modules/module', 'Replace existing files'),
        ];
    }
    
    /**
     * Gets module cloning done.
     * @return boolean 
     */
    public function run()
    {
        return $this->copy() && $this->replace() ;
        
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
        if (!$this->replace && is_dir($destination)) { // if replace is disabled we will skip existing files
            $this->keepFiles = FileHelper::findFiles($destination);
        }
        FileHelper::copyDirectory($source, $destination, [
            'except' => ['.git/'],
            'fileMode' => 0775,
            'beforeCopy' => function ($from, $to) {
                return $this->replace || !file_exists($to) || !is_file($to);
            }
        ]);
        return true;
    }

    /**
     * Replaces all new module classes content with empty template.
     * @return boolean
     */
    private function replace()
    {
        $destination = Yii::getAlias($this->destination);
        $destinationModuleName = $this->getDestinationModuleName();
        foreach (FileHelper::findFiles($destination) as $path) {
            if (!$this->replace && in_array($path, $this->keepFiles)) {
                continue;
            }
            if (!preg_match('/^.*\.php$/', $path, $matches)) { // php file.
                continue;
            } else if (preg_match('/^.*\W([A-Z]\w+)\.php$/', $path, $matches)) { // Class file.
                file_put_contents($path, $this->createClassContent($matches[1], $path));
            } else if (self::isMigration($path)) { // Class file.
                file_put_contents($path, $this->updateFileContent($path));
                if ($destinationModuleName) {
                    $this->renameClassFile($path, function($className) use ($destinationModuleName){
                        return $className . '_' . $destinationModuleName;
                    });
                }
            } else if ($this->inheritContent) {
                file_put_contents($path, $this->createFileContent($path));
            } else {
                file_put_contents($path, $this->updateFileContent($path));
            }
        }
        return true;
    }

    protected function createClassContent($className, $path)
    {
        $destination = Yii::getAlias($this->destination);
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .
            'templates' . DIRECTORY_SEPARATOR . 'class_clone_template');
        $addedNamespace = str_replace(
            [$destination, DIRECTORY_SEPARATOR . $className .'.php', '/'],
            ['', '', '\\'],
            $path
        );
        $fileNamespace = $this->getNewNamespace() . $addedNamespace;
        $oldClassName = $this->getOldNamespace() . $addedNamespace . '\\' . $className;
        return  "<?php " . str_replace(
            ['$namespace', '$class', '$oldClass'],
            [$fileNamespace, $className, $oldClassName],
            $template
        );
    }

    private function createFileContent($path)
    {
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .
            'templates' .DIRECTORY_SEPARATOR . 'file_clone_template');
        $pathEnd = str_replace(Yii::getAlias($this->destination), '', $path);
        $oldFile = $this->source . str_replace('\\', '/', $pathEnd);
        return  "<?php " . str_replace('$oldFile', $oldFile, $template);
    }

    protected function updateFileContent($path)
    {
        return str_replace(
            $this->getOldNamespace(),
            $this->getNewNamespace(),
            file_get_contents($path)
        );
    }

    private function getNewNamespace()
    {
        return str_replace(['@', '/'], ['', '\\'], $this->destination);
    }

    private function getOldNamespace()
    {
        return str_replace(['@', '/'], ['', '\\'], $this->source);
    }

    private function getDestinationModuleName()
    {
        return preg_match('/.*\/modules\/(.*\/)?([-\w]+)$/', $this->destination, $matches)
            ? $matches[2] : null;
    }

    /**
     * @param $path
     * @return boolean
     */
    private static function isMigration($path)
    {
        $migrationPath = preg_quote(DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR, '/');
        return preg_match('/^.*'.$migrationPath.'\w+\.php$/', $path);
    }

    private function renameClassFile($path, $callback)
    {
        $pathArray = explode(DIRECTORY_SEPARATOR, $path);
        $fileName = array_pop($pathArray);
        $className = str_replace('.php', '', $fileName);
        $newClassName = call_user_func($callback, $className);
        array_push($pathArray, str_replace($className, $newClassName, $fileName)); //new filename
        $newPath = implode(DIRECTORY_SEPARATOR, $pathArray);
        if (!$this->replace && file_exists($newPath)) {
            return unlink($path);
        }
        file_put_contents($path, str_replace(
            "class $className",
            "class $newClassName",
            file_get_contents($path)
        ));
        rename($path, $newPath);
    }
}
