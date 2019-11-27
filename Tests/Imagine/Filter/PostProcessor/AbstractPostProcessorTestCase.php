<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Tests\AbstractTest;

abstract class AbstractPostProcessorTestCase extends AbstractTest
{
    public static function getPostProcessAsFileExecutable(): string
    {
        return realpath(__DIR__.'/../../../Fixtures/bin/post-process-as-file.bash');
    }

    public static function getPostProcessAsFileFailingExecutable(): string
    {
        return realpath(__DIR__.'/../../../Fixtures/bin/post-process-as-file-error.bash');
    }

    public static function getPostProcessAsStdInExecutable(): string
    {
        return realpath(__DIR__.'/../../../Fixtures/bin/post-process-as-stdin.bash');
    }

    public static function getPostProcessAsStdInErrorExecutable(): string
    {
        return realpath(__DIR__.'/../../../Fixtures/bin/post-process-as-stdin-error.bash');
    }

    abstract protected function getPostProcessorInstance(array $parameters = []);

    protected function getBinaryInterfaceMock(): BinaryInterface
    {
        return $this
            ->getMockBuilder(BinaryInterface::class)
            ->getMock();
    }

    protected function assertTemporaryFile(string $content, string $file, string $context, array $options = []): void
    {
        $this->assertFileExists($file);
        $this->assertStringContainsString($context, $file);
        $this->assertSame($content, file_get_contents($file));

        if (isset($options['temp_dir'])) {
            $this->assertStringContainsString($options['temp_dir'], $file);
        }
    }

    /**
     * @param \ReflectionObject|string $object
     */
    protected function getProtectedReflectionMethodVisible($object, string $method): \ReflectionMethod
    {
        if ($object instanceof \ReflectionObject) {
            $r = $object;
        } else {
            $r = new \ReflectionObject($object);
        }

        $m = $r->getMethod($method);
        $m->setAccessible(true);

        return $m;
    }

    /**
     * @param \ReflectionObject|string $object
     */
    protected function getProtectedReflectionPropertyVisible($object, string $property): \ReflectionProperty
    {
        if ($object instanceof \ReflectionObject) {
            $r = $object;
        } else {
            $r = new \ReflectionObject($object);
        }

        $p = $r->getProperty($property);
        $p->setAccessible(true);

        return $p;
    }

    protected function getProcessArguments(array $options): array
    {
        $arguments = $this
            ->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance(), 'getProcessArguments')
            ->invokeArgs($processor, [$options]);

        return $arguments;
    }
}
