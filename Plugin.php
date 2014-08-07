<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 8/6/14
 * Time: 7:11 PM
 */

namespace bariew\moduleModule;


use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $path = rtrim($composer->getConfig()->get('vendor-dir'), '/') . '/yiisoft/extensions.php';
        $data = require $path;
        $data['bariew/yii2-config-cms-module'] = array (
            'name' => 'bariew/yii2-config-cms-module',
            'version' => '9999999-dev',
            'alias' =>
              array (
                  '@bariew/configModule' =>  "<vendor-dir> . '/bariew/yii2-config-cms-module'",
              ),
            'bootstrap' => 'bariew\\configModule\\ConfigBootstrap',
        );
        $content = "<?php\n\n <vendor-dir> = dirname(__DIR__);\n\n return " . var_export($data, true) . ";\n";
        file_put_contents($path, str_replace('<vendor-dir>', '$vendorDir', $content));
    }

    public static function test()
    {
        $path = \Yii::getAlias('@vendor/yiisoft/extensions.php');
        $data = require $path;
        $data['bariew/yii2-config-cms-module'] = array (
            'name' => 'bariew/yii2-config-cms-module',
            'version' => '9999999-dev',
            'alias' =>
                array (
                    '@bariew/configModule' =>  "<vendor-dir> . '/bariew/yii2-config-cms-module'",
                ),
            'bootstrap' => 'bariew\\configModule\\ConfigBootstrap',
        );
        $content = "<?php\n\n <vendor-dir> = dirname(__DIR__);\n\n return " . var_export($data, true) . ";\n";
        file_put_contents($path, str_replace('<vendor-dir>', '$vendorDir', $content));
    }
} 