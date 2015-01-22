<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 19.01.15
 * Time: 10:28
 */

namespace bariew\moduleModule\models;

use bariew\moduleModule\Module;
use yii\console\controllers\MigrateController;

class Snapshot
{
    private $paths = [
        '@app/config/local/main.php',
        '@app/migrations',
        '@app/composer.json',
        '@app/composer.lock',
        '@app/web/themes'
    ];

    public function compact()
    {
        $this->createMigrations();
        $this->createArchive($this->paths);
        $this->sendArchive();
    }

    public function extract()
    {
        $this->extractArchive($this->paths);
        Composer::installAll();
        $controller = new MigrateController('migrate', new Module('module'));
        $controller->actionUp();
    }
} 