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

abstract class AbstractWebTestCase extends WebTestCase
{
    public static function getKernelClass(): string
    {
        require_once __DIR__.'/app/AppKernel.php';

        return AppKernel::class;
    }

    protected function getService(string $name): ?object
    {
        if (property_exists($this, 'container')) {
            return static::$container->get($name);
        }

        return static::$kernel->getContainer()->get($name);
    }

    protected function getParameter(string $name)
    {
        if (property_exists($this, 'container')) {
            return static::$container->getParameter($name);
        }

        return static::$kernel->getContainer()->getParameter($name);
    }

    protected function getPrivateProperty(object $object, string $name)
    {
        $r = new \ReflectionObject($object);

        $p = $r->getProperty($name);
        $p->setAccessible(true);

        return $p->getValue($object);
    }
}
