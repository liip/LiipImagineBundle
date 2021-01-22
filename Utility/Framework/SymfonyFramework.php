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

/**
 * @internal
 */
final class SymfonyFramework
{
    public static function getContainerResolvableRootWebPath(): string
    {
        return sprintf('%%kernel.project_dir%%/%s', self::isKernelLessThan(4) ? 'web' : 'public');
    }

    public static function isKernelGreaterThanOrEqualTo(int $major, int $minor = null, int $patch = null): bool
    {
        return static::kernelVersionCompare('>=', $major, $minor, $patch);
    }

    public static function isKernelLessThan(int $major, int $minor = null, int $patch = null): bool
    {
        return static::kernelVersionCompare('<', $major, $minor, $patch);
    }

    private static function kernelVersionCompare(string $operator, int $major, int $minor = null, int $patch = null): bool
    {
        return version_compare(Kernel::VERSION_ID, sprintf("%d%'.02d%'.02d", $major, $minor ?: 0, $patch ?: 0), $operator);
    }
}
