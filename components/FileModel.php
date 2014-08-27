<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 8/25/14
 * Time: 6:47 PM
 */

namespace bariew\moduleModule\components;


use yii\base\Model;

class FileModel extends Model
{

    protected $_attributes = [];

    public function getPath()
    {
        return '';
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $content = '<?php return ' . var_export($this->attributes, true) . '; ';
        return file_put_contents($this->getPath(), $content) !== false;
    }

    public function init()
    {
        $this->setFileAttributes($this->getPath());
        parent::init();
    }

    protected function setFileAttributes($filePath)
    {
        $attributes = file_exists($filePath) ? require $filePath : [];
        foreach ($attributes as $name => $value) {
            if (is_array($value) || is_object($value) || is_callable($value)) {
                continue;
            }
            $this->_attributes[$name] = $value;
        }
    }

    public function attributes()
    {
        return array_keys($this->_attributes);
    }

    /**
     * Returns the attribute names that are safe to be massively assigned in the current scenario.
     * @return string[] safe attribute names
     */
    public function safeAttributes()
    {
        return $this->attributes();
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        return $this->_attributes[$name] = $value;
    }
} 