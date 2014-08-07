<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 8/6/14
 * Time: 7:04 PM
 */

namespace bariew\moduleModule;


use bariew\moduleModule\models\Package;
use yii\composer\Installer;

class ComposerInstaller extends Installer
{
    public function addModulePackage($package)
    {
        return parent::addPackage($package);
    }
} 