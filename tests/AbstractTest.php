<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTest extends TestCase
{
    protected ?Filesystem $filesystem = null;

    protected string $fixturesPath = '';

    protected string $temporaryPath;

    protected function setUp(): void
    {
        $this->fixturesPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'Fixtures');
        $this->temporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'liip_imagine_test';
        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->temporaryPath)) {
            $this->filesystem->remove($this->temporaryPath);
        }

        $this->filesystem->mkdir($this->temporaryPath);
    }

    protected function tearDown(): void
    {
        if (!$this->filesystem) {
            return;
        }

        if ($this->filesystem->exists($this->temporaryPath)) {
            $this->filesystem->remove($this->temporaryPath);
        }
    }

    protected function getVisibilityRestrictedMethod(object $object, string $name): \ReflectionMethod
    {
        $r = new \ReflectionObject($object);

        $m = $r->getMethod($name);
        $m->setAccessible(true);

        return $m;
    }
}
