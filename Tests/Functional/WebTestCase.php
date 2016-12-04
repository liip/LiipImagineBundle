<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    public function setUp()
    {
        if (!class_exists('Symfony\Component\Asset\Package')) {
            $this->markTestSkipped('The symfony/asset PHP library is not available.');
        }
        if (!class_exists('Symfony\Component\Translation\Translator')) {
            $this->markTestSkipped('The symfony/translation PHP library is not installed.');
        }
        parent::setUp();
    }

    /**
     * @return string
     */
    public static function getKernelClass()
    {
        require_once __DIR__.'/app/AppKernel.php';

        return 'Liip\ImagineBundle\Tests\Functional\app\AppKernel';
    }
}
