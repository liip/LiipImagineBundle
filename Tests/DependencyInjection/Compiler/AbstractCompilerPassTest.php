<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\AbstractCompilerPass;
use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\DependencyInjection\Compiler\Compiler;
use Symfony\Component\DependencyInjection\Compiler\LoggingFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\AbstractCompilerPass
 */
class AbstractCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @param string[] $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractCompilerPass
     */
    private function createAbstractCompilerPassMock(array $methods = array())
    {
        return $this
            ->getMockBuilder(AbstractCompilerPass::class)
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param string[] $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    private function createContainerBuilderMock(array $methods = array())
    {
        return $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param string[] $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Compiler
     */
    private function createCompilerMock(array $methods = array())
    {
        return $this
            ->getMockBuilder(Compiler::class)
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param string[] $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Compiler
     */
    private function createLoggingFormatterMock(array $methods = array())
    {
        return $this
            ->getMockBuilder(LoggingFormatter::class)
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param string[] $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Definition
     */
    private function createDefinitionMock(array $methods = array())
    {
        return $this
            ->getMockBuilder(Definition::class)
            ->setMethods($methods)
            ->getMock();
    }

    public function testCompilerLogging()
    {
        $pass = $this->createAbstractCompilerPassMock();

        $message = 'Compiler log: %d %s message';
        $messageReplaces = array(1, 'foo-bar');
        $messageCompiled = vsprintf($message, $messageReplaces);

        if (SymfonyFramework::hasDirectContainerBuilderLogging()) {
            $container = $this->createContainerBuilderMock();
            $container
                ->expects($this->atLeastOnce())
                ->method('log')
                ->with($pass, $messageCompiled);
        } else {
            $container = $this->createContainerBuilderMock(array('getCompiler'));
            $formatter = $this->createLoggingFormatterMock(array('format'));
            $formatter
                ->expects($this->atLeastOnce())
                ->method('format')
                ->with($pass, $messageCompiled);

            $compiler = $this->createCompilerMock(array('addLogMessage', 'getLoggingFormatter'));
            $compiler
                ->expects($this->atLeastOnce())
                ->method('addLogMessage');
            $compiler
                ->expects($this->atLeastOnce())
                ->method('getLoggingFormatter')
                ->willReturn($formatter);

            $container
                ->expects($this->atLeastOnce())
                ->method('getCompiler')
                ->willReturn($compiler);
        }

        $log = $this->getVisibilityRestrictedMethod($pass, 'log');
        $log->invoke($pass, $container, $message, $messageReplaces);
    }
}
