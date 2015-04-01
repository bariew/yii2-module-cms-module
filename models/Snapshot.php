<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 19.01.15
 * Time: 10:28
 */

namespace bariew\moduleModule\models;

use yii\base\Model;
use yii\db\Query;

class Snapshot extends Model
{
    public $onlyMigration = true;
    public $tables;

    public $archivePath = '@app/runtime/snapshot.zip';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->tables = array_diff(self::tableList(), ['migration']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['onlyMigration'], 'boolean'],
            [['tables'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'onlyMigration' => \Yii::t('modules/module', 'Migrations only'),
            'tables' => \Yii::t('modules/module', 'Migration tables'),
        ];
    }

    public static function tableList()
    {
        $tables = \Yii::$app->db->schema->getTableNames();
        return array_combine($tables, $tables);
    }

    public function getExcludePaths()
    {
        $appDir = basename(\Yii::getAlias('@app'));
        return [
            '/'.$appDir.'\/nbproject/',
            '/'.$appDir.'\/\.git/',
            '/'.$appDir.'\/\.idea/',
            '/'.$appDir.'\/vendor/',
            '/'.$appDir.'\/runtime\/(?!\.gitignore)/',
            '/'.$appDir.'\/web\/assets\/(?!\.gitignore)/',
            '/'.$appDir.'\/web\/files\/(?!\.gitignore)/',
        ];
    }


    public function compact()
    {
        $path = \Yii::getAlias($this->archivePath);
        $this->createMigrations();
        if ($this->onlyMigration) {
            return true;
        }
        $this->createArchive(\Yii::getAlias('@app'), $this->getExcludePaths());
        \Yii::$app->response->sendFile($path, 'snapshot_' . date('Y-m-d') . '.zip');
        unlink($path);
        \Yii::$app->end();
    }

    private function createMigrations()
    {
        $result = [];
        foreach ($this->tables as $table) {
            $result[$table] = (new Query())->from([$table])->all();
        }
        $name = 'm' . gmdate('ymd_His') . '_snapshot';
        $file = \Yii::getAlias('@app/migrations') . DIRECTORY_SEPARATOR . $name . '.php';
        $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR
            . 'templates' . DIRECTORY_SEPARATOR . 'snapshot_migration_template');
        $content = str_replace(['{{name}}', '{{data}}'], [$name, var_export($result, true)], $content);
        file_put_contents($file, '<?php ' . $content);
        chmod($file, 0777);
        return $this;
    }

    /**
     * @param $path
     * @param $exclude
     * @return $this
     */
    private function createArchive($path, $exclude)
    {
        $zip = new \ZipArchive();
        $zip->open(\Yii::getAlias($this->archivePath), \ZipArchive::CREATE);
        $this->zipRecursive($zip, $path, '', $exclude);
        return $this;
    }


    /**
     * @param \ZipArchive $zip
     * @param $src
     * @param string $innerPath
     * @param array $exclude
     * @return bool
     */
    private function zipRecursive(\ZipArchive $zip, $src, $innerPath = '', $exclude = [])
    {
        $localName = $innerPath
            ? $innerPath . DIRECTORY_SEPARATOR . basename($src)
            : basename($src);
        foreach ($exclude as $pattern) {
            if (preg_match($pattern, $localName)) {
                return true;
            }
        }
        if (is_file($src)) {
            return $zip->addFile($src, $localName);
        }
        $zip->addEmptyDir($localName);
        foreach (scandir($src) as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $this->zipRecursive($zip, $src . DIRECTORY_SEPARATOR . $file, $localName, $exclude);
        }
    }
} 