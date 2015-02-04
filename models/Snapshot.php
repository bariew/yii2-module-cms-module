<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 19.01.15
 * Time: 10:28
 */

namespace bariew\moduleModule\models;

use yii\db\Query;

class Snapshot
{
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

    public $archivePath = '@app/runtime/snapshot.zip';

    public function compact()
    {
        $path = \Yii::getAlias($this->archivePath);
        $this->createMigrations()
            ->createArchive(\Yii::getAlias('@app'), $this->getExcludePaths());
        \Yii::$app->response->sendFile($path, 'snapshot_' . date('Y-m-d') . '.zip');
        unlink($path);
        \Yii::$app->end();
    }

    private function createMigrations()
    {
        $result = [];
        foreach (\Yii::$app->db->schema->getTableNames() as $table) {
            if ($table == 'migration') {
                continue;
            }
            $result[$table] = (new Query())->from([$table])->all();
        }
        $name = 'm' . gmdate('ymd_His') . '_snapshot';
        $file = \Yii::getAlias('@app/migrations') . DIRECTORY_SEPARATOR . $name . '.php';
        $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'snapshot_migration_template.php');
        $content = str_replace(['{{name}}', '{{data}}'], [$name, var_export($result, true)], $content);
        file_put_contents($file, '<?php ' . $content);
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