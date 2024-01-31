#!/usr/bin/env php
<?php

use App\Model;
use Nette\Utils\Finder;
use Nette\Utils\FileSystem;

# Autoloading tříd přes Composer - tedy i naší Bootstrap třídy
require __DIR__ . '/../vendor/autoload.php';

# Necháme konfigurátor, aby nám sestavil DI kontejner
$container = App\Bootstrap::bootForCron()
	->createContainer();

$c = $container->callMethod(function (App\Model\Videos $videos) {

    
//    dumpe(getcwd());
    $dir = FileSystem::normalizePath("../www/video/");
    
    foreach (Finder::findFiles('*.webm')->from($dir) as $key => $file) {
	// $key je řetězec s názvem souboru včetně cesty
	// $file je objekt SplFileInfo
        $path = FileSystem::normalizePath($key);
        $blocks = explode(DIRECTORY_SEPARATOR, $path);
        $start = array_search("video", $blocks);
        
        $pro_id = $blocks[$start+1];
        $file = $blocks[count($blocks)-1];
        $side = $blocks[count($blocks)-2];
        
        $time = explode("_", $file);
        $time = explode("+", $time[1]);
        $time = $time[0];
//        echo $time . "\r\n";
        $time = str_replace("T", "", $time);
        $date = DateTime::createFromFormat('YmdHisu', $time);

        // With microseconds
//        echo $date->format("Y-m-d H:i:s").'.'.$date->format('u');
//        $time = str_replace("T", "", $time);
//        echo $time . "\r\n";+
//        echo date("Y-m-d H:i:s.u", strtotime($time/1000)) . "\r\n";
//        dump($blocks);
        $duration = $videos->getDuration($path);
//        echo $duration["playtime_seconds"] . "\r\n";
        echo str_replace($dir, "", $path) . "\r\n";
        if($pro_id == 9)
        {
            $vals = [];
            $vals[$videos::COLUMN_VID_NAME] = str_replace($dir, "", $path);
            $vals[$videos::COLUMN_VID_START] = $date->format("Y-m-d H:i:s");
            $vals[$videos::COLUMN_VID_END] = date("Y-m-d H:i:s", $date->getTimestamp() + ceil($duration["playtime_seconds"]));
            $vals[$videos::COLUMN_VID_PLAYTIME] = $duration["playtime_seconds"];
            $vals[$videos::COLUMN_VID_SIDE] = $side;
            $vals[$videos::COLUMN_PRO_ID] = $pro_id;

            $check = $videos->check($vals);
            if(!$check)
            {
                $videos->insert($vals);
            }
        }
    }
});