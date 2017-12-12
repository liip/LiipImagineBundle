<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ChildDefinition;

trait ChildDefinitionTrait
{
    /**
     * @return ChildDefinition
     */
    private function getChildDefinition($parent)
    {
        return new ChildDefinition($parent);
    }
}
