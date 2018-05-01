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

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\AbstractCompilerPass
 */
class AbstractCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testCompilerLogging()
    {
        $pass = $this->createAbstractCompilerPassMock();

        $message = 'Compiler log %s with %d substitutions';
        $replace = ['entry', 2];
        $expects = vsprintf('[liip/imagine-bundle] '.$message, $replace);

        $container = $this->createContainerBuilderMock();
        $this->expectContainerLogMethodCalledOnce($container, $pass, $expects);

        $log = $this->getVisibilityRestrictedMethod($pass, 'log');
        $log->invoke($pass, $container, $message, ...$replace);
    }

    /**
     * @param string[] $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractCompilerPass
     */
    private function createAbstractCompilerPassMock(array $methods = [])
    {
        return $this
            ->getMockBuilder(AbstractCompilerPass::class)
            ->setMethods($methods)
            ->getMock();
    }
}
