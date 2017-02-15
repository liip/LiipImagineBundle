<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Utility\Framework;

use Symfony\Component\HttpKernel\Kernel;

class SymfonyFramework
{
    /**
     * @return bool
     */
    public static function hasDefinitionSharing()
    {
        return method_exists('\Symfony\Component\DependencyInjection\Definition', 'setShared')
            && method_exists('\Symfony\Component\DependencyInjection\Definition', 'isShared');
    }

    /**
     * @return bool
     */
    public static function hasDefinitionScoping()
    {
        return method_exists('\Symfony\Component\DependencyInjection\Definition', 'setScope')
            && method_exists('\Symfony\Component\DependencyInjection\Definition', 'getScope');
    }

    /**
     * @return bool
     */
    public static function hasDirectContainerBuilderLogging()
    {
        return method_exists('\Symfony\Component\DependencyInjection\ContainerBuilder', 'log');
    }

    /**
     * @param int      $major
     * @param int|null $minor
     * @param int|null $patch
     *
     * @return bool
     */
    public static function isKernelGreaterThanOrEqualTo($major, $minor = null, $patch = null)
    {
        return static::kernelVersionCompare('>=', $major, $minor, $patch);
    }

    /**
     * @param int      $major
     * @param int|null $minor
     * @param int|null $patch
     *
     * @return bool
     */
    public static function isKernelLessThan($major, $minor = null, $patch = null)
    {
        return static::kernelVersionCompare('<', $major, $minor, $patch);
    }

    /**
     * @param string   $operator
     * @param int      $major
     * @param int|null $minor
     * @param int|null $patch
     *
     * @return bool
     */
    private static function kernelVersionCompare($operator, $major, $minor = null, $patch = null)
    {
        $vernum = $major;
        $kernel = Kernel::MAJOR_VERSION;

        if ($minor) {
            $vernum .= '.'.$minor;
            $kernel .= '.'.Kernel::MINOR_VERSION;

            if ($patch) {
                $vernum .= '.'.$patch;
                $kernel .= '.'.Kernel::RELEASE_VERSION;
            }
        }

        return version_compare($kernel, $vernum, $operator);
    }
}
