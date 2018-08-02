<?php
namespace OpenGift\Bitrix;

class Composer
{
    public static function install($dir = false)
    {
        if (!$dir) $dir = $_SERVER['DOCUMENT_ROOT'];
        exec('cd ' . $dir . ' && curl -sS https://getcomposer.org/installer | php 2>&1', $output);
        sleep(10);
        return $output;
    }

    public static function installDependencies($dir = false)
    {
        if (!$dir) $dir = $_SERVER['DOCUMENT_ROOT'];
        return shell_exec('cd ' . $dir . ' && php composer.phar install');
    }

    public static function installGeoIPAPI()
    {
        $output = '';
        exec('php composer.phar require geoip2/geoip2:~2.0');
        return $output;
    }

    public static function init()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
    }
}