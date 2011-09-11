<?php

spl_autoload_register(function($class) {
    $class = ltrim($class, '\\');
    if (0 === strpos($class, 'Avalanche\Bundle\ImagineBundle\\')) {
        $file = __DIR__.'/../'.str_replace('\\', '/', substr($class, strlen('Avalanche\Bundle\ImagineBundle\\'))).'.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

if (!defined('SYMFONY_SRC_DIR') || 'NOT_SET' === SYMFONY_SRC_DIR) {
    throw new \RuntimeException('You must set the Symfony src dir');
}

if (!defined('IMAGINE_SRC_DIR') || 'NOT_SET' === IMAGINE_SRC_DIR) {
    throw new \RuntimeException('You must set the Imagine src dir');
}

require_once SYMFONY_SRC_DIR.'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('Symfony', SYMFONY_SRC_DIR);
$loader->registerNamespace('Imagine', IMAGINE_SRC_DIR);
$loader->register();
