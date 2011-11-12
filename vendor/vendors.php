#!/usr/bin/env php
<?php

set_time_limit(0);

if (!is_dir($vendorDir = __DIR__)) {
    mkdir($vendorDir, 0777, true);
}

$deps = array(
    array('symfony', 'http://github.com/symfony/symfony', 'v2.0.5'),
    array('imagine', 'http://github.com/avalanche123/Imagine', 'v0.2.6'),
);

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    }

    if ($rev) {
        system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
    }
}
