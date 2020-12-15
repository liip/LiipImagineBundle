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

use Liip\ImagineBundle\Tests\Functional\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;

abstract class AbstractWebTestCase extends WebTestCase
{
    /**
     * @return string
     */
    public static function getKernelClass()
    {
        require_once __DIR__.'/app/AppKernel.php';

        return AppKernel::class;
    }

    /**
     * @return object
     */
    protected function getService(string $name)
    {
        return static::$kernel->getContainer()->get($name);
    }

    /**
     * @return mixed
     */
    protected function getParameter(string $name)
    {
        return static::$kernel->getContainer()->getParameter($name);
    }

    /**
     * @param object $object
     *
     * @return mixed
     */
    protected function getPrivateProperty($object, string $name)
    {
        $r = new \ReflectionObject($object);

        $p = $r->getProperty($name);
        $p->setAccessible(true);

        return $p->getValue($object);
    }
}
