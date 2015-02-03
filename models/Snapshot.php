<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 19.01.15
 * Time: 10:28
 */

namespace bariew\moduleModule\models;

use yii\db\Query;
use yii\web\UploadedFile;

class Snapshot
{
    private $paths = [
        'localConfig'  => '@app/config/local/main.php',
        'migrations'   => '@app/migrations',
        'modules'      => '@app/modules',
        'composerJson' => '@app/composer.json',
        'composerLock' => '@app/composer.lock',
        'themes'       => '@app/web/themes',
    ];

    private $archivePath = '@app/runtime/snapshot.zip';

    public function compact()
    {
        $path = \Yii::getAlias($this->archivePath);
        $this->createMigrations()
            ->createArchive($this->paths);
        \Yii::$app->response->sendFile($path, 'snapshot_' . date('Y-m-d') . '.zip');
        unlink($path);
        \Yii::$app->end();
    }

    public function extract(UploadedFile $file)
    {
        $this->extractArchive($file->tempName, \Yii::getAlias('@app'));
        Composer::installAll();
    }

    private function extractArchive($source, $destination)
    {
        $zip = new \ZipArchive();
        $zip->open($source);
        $zip->extractTo($destination);
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
        $file = \Yii::getAlias($this->paths['migrations']) . DIRECTORY_SEPARATOR . $name . '.php';
        $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'snapshot_migration_template.php');
        $content = str_replace(['{{name}}', '{{data}}'], [$name, var_export($result, true)], $content);
        file_put_contents($file, '<?php ' . $content);
        return $this;
    }

    /**
     * @param $paths
     * @return $this
     */
    private function createArchive($paths)
    {
        $zip = new \ZipArchive();
        $zip->open(\Yii::getAlias($this->archivePath), \ZipArchive::CREATE);
        foreach ($paths as $alias) {
            $path = \Yii::getAlias($alias);
            if (is_file($path)) {
                $innerPath = str_replace([
                    \Yii::getAlias('@app'),
                    DIRECTORY_SEPARATOR . basename($path)
                ], ['', ''], $path);
            } else {
                $innerPath = '';
            }
            $this->zipRecursive($zip, $path, $innerPath);
        }
        return $this;
    }


    /**
     * @param \ZipArchive $zip
     * @param $src
     * @param string $innerPath
     * @return bool
     */
    private function zipRecursive(\ZipArchive $zip, $src, $innerPath = '')
    {
        $localName = $innerPath
            ? $innerPath . DIRECTORY_SEPARATOR . basename($src)
            : basename($src);
        if (is_file($src)) {
            return $zip->addFile($src, $localName);
        }
        $zip->addEmptyDir($localName);
        foreach (scandir($src) as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $this->zipRecursive($zip, $src . DIRECTORY_SEPARATOR . $file, $localName);
        }
    }
} 