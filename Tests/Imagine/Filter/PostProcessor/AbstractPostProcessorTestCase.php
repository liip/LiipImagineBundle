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

use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Tests\AbstractTest;

abstract class AbstractPostProcessorTestCase extends AbstractTest
{
    /**
     * @param array $parameters
     *
     * @return PostProcessorInterface
     */
    abstract protected function getPostProcessorInstance(array $parameters = array());

    /**
     * @return string
     */
    public static function getPostProcessAsFileExecutable()
    {
        return realpath(__DIR__.'/../../../Fixtures/bin/post-process-as-file.bash');
    }

    /**
     * @return string
     */
    public static function getPostProcessAsFileFailingExecutable()
    {
        return realpath(__DIR__.'/../../../Fixtures/bin/post-process-as-file-error.bash');
    }

    /**
     * @return string
     */
    public static function getPostProcessAsStdInExecutable()
    {
        return realpath(__DIR__.'/../../../Fixtures/bin/post-process-as-stdin.bash');
    }

    /**
     * @return string
     */
    public static function getPostProcessAsStdInErrorExecutable()
    {
        return realpath(__DIR__.'/../../../Fixtures/bin/post-process-as-stdin-error.bash');
    }

    /**
     * @return \Liip\ImagineBundle\Binary\BinaryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBinaryInterfaceMock()
    {
        return $this
            ->getMockBuilder('\Liip\ImagineBundle\Binary\BinaryInterface')
            ->getMock();
    }

    /**
     * @param string $content
     * @param string $file
     * @param string $context
     * @param array  $options
     */
    protected function assertTemporaryFile($content, $file, $context, array $options = array())
    {
        $this->assertFileExists($file);
        $this->assertContains($context, $file);
        $this->assertSame($content, file_get_contents($file));

        if (isset($options['temp_dir'])) {
            $this->assertContains($options['temp_dir'], $file);
        }
    }

    /**
     * @param \ReflectionObject|string $object
     * @param string                   $method
     *
     * @return \ReflectionMethod
     */
    protected function getProtectedReflectionMethodVisible($object, $method)
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
     * @param string                   $property
     *
     * @return \ReflectionProperty
     */
    protected function getProtectedReflectionPropertyVisible($object, $property)
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

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getSetupProcessBuilderArguments(array $options)
    {
        $builder = $this
            ->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance(), 'setupProcessBuilder')
            ->invokeArgs($processor, array($options));

        return $this
            ->getProtectedReflectionPropertyVisible($builder, 'arguments')
            ->getValue($builder);
    }
}
