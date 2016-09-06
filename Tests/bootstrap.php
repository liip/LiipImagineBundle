<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace {
    if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
        echo <<<'EOM'
You must set up the project dependencies by running the following commands:

curl -s http://getcomposer.org/installer | php
php composer.phar install

EOM;
        exit(1);
    }
}

namespace Symfony\Component\ExpressionLanguage {
    if (interface_exists('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface')) {
        return;
    }

    interface ExpressionFunctionProviderInterface
    {
    }
}
