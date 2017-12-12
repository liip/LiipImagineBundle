<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;

abstract class FactoryTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param Definition $definition
     */
    protected function assertInstanceOfChildDefinition(Definition $definition)
    {
        $this->assertInstanceOf(ChildDefinition::class, $definition);
    }
}
