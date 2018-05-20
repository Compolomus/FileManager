<?php

namespace Test;

use Compolomus\FileManager\FileManager;
use SplFileInfo;

require_once __DIR__ . '/../vendor/autoload.php';

$fm = new FileManager(['chroot' => './']);

echo '<pre>' . print_r($fm->ls('/usr/'), true) . '</pre>';
echo '<pre>' . print_r($fm, true) . '</pre>';


#$fm->delete(new SplFileInfo('/var/www/html/pr/fm/extra/2/test2'));
