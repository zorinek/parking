#!/usr/bin/env php
<?php

use App\Model;
use Nette\Utils\Finder;

# Autoloading tříd přes Composer - tedy i naší Bootstrap třídy
require __DIR__ . '/../vendor/autoload.php';

# Necháme konfigurátor, aby nám sestavil DI kontejner
$container = App\Bootstrap::bootForCron()
	->createContainer();

$c = $container->callMethod(function () {
    $d = getcwd();
    $dir = "../www/video/10/";
    foreach (Finder::findFiles('*')->from($dir) as $key => $file) {
        $blocks = explode(".webm", $key);
        if(count($blocks) > 1 && is_file($blocks[0] . '.flv'))
        {
//        echo $d . '/../lib/ffmpeg/ffmpeg.exe -i "' . $key . '" -c:v libvpx-vp9 -speed 16 -deadline realtime   "' . $blocks[0] . '".webm"' . "\r\n\r\n";
//            $ret = shell_exec($d . '/../lib/ffmpeg/ffmpeg.exe -i "' . $key . '" -c:v libvpx-vp9 -crf 45 -speed 16 -deadline realtime   "' . $blocks[0] . '.webm"');
//        dumpe($ret);
            unlink($blocks[0] . ".flv");
            echo $blocks[0] . ".flv\r\n";
//            die();
        }
    }
});