<?php

namespace Test;

use Compolomus\FileManager\FileManager;
use SplFileInfo;

require_once __DIR__ . '/../vendor/autoload.php';

$fm = new FileManager(['chroot' => __DIR__]);

#echo '<pre>' . print_r($fm->ls(__DIR__ . DIRECTORY_SEPARATOR . 'test'), true) . '</pre>';
echo '<pre>' . print_r($fm, true) . '</pre>';
//mkdir(__DIR__ . '/test3', 0777, true);
//$fm->mkdir(__DIR__ . '/test3/mkdir');
//$fm->itemList();
//echo '<pre>' . print_r($fm, true) . '</pre>';


#$fm->delete(new SplFileInfo('/var/www/html/pr/fm/extra/2/test2'));
