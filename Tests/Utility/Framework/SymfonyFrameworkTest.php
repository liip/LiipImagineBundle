<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Utility\Framework;

use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;

/**
 * @covers \Liip\ImagineBundle\Utility\Framework\SymfonyFramework
 */
class SymfonyFrameworkTest extends \PHPUnit_Framework_TestCase
{
    public function testHasDefinitionSharing()
    {
        if (SymfonyFramework::isKernelGreaterThanOrEqualTo(2, 8)) {
            $this->assertTrue(SymfonyFramework::hasDefinitionSharing());
        } else {
            $this->assertFalse(SymfonyFramework::hasDefinitionSharing());
        }
    }

    public function testHasDefinitionScoping()
    {
        if (SymfonyFramework::isKernelGreaterThanOrEqualTo(3, 0)) {
            $this->assertFalse(SymfonyFramework::hasDefinitionScoping());
        } else {
            $this->assertTrue(SymfonyFramework::hasDefinitionScoping());
        }
    }

    public function testHasDirectContainerBuilderLogging()
    {
        if (SymfonyFramework::isKernelGreaterThanOrEqualTo(3, 3)) {
            $this->assertTrue(SymfonyFramework::hasDirectContainerBuilderLogging());
        } else {
            $this->assertFalse(SymfonyFramework::hasDirectContainerBuilderLogging());
        }
    }

    public function testIsKernelGreaterThanOrEqualToOrLessThan()
    {
        if (false === $v = getenv('SYMFONY_VERSION')) {
            $this->markTestSkipped('Requires SYMFONY_VERSION environment variable.');
        }

        if (1 !== preg_match('{(?<major>[0-9]+)\.(?<minor>[0-9]+)\.x(?:-dev)?}', $v, $matches)) {
            $this->markTestSkipped('Requires SYMFONY_VERSION in format x.x.x[-dev]');
        }

        $this->assertTrue(SymfonyFramework::isKernelGreaterThanOrEqualTo($matches['major'], $matches['minor']));
        $this->assertFalse(SymfonyFramework::isKernelLessThan($matches['major'], $matches['minor']));
    }

    public function testIsKernelLessThan()
    {
        $this->assertTrue(SymfonyFramework::isKernelLessThan(100, 100, 100));
        $this->assertFalse(SymfonyFramework::isKernelLessThan(1, 1, 1));
    }
}
