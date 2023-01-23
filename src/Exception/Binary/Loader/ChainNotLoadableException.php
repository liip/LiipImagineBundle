<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Exception\Binary\Loader;

final class ChainNotLoadableException extends NotLoadableException
{
    public function __construct(string $path, ChainAttemptNotLoadableException ...$exceptions)
    {
        parent::__construct(self::compileExceptionMessage($path, ...$exceptions));
    }

    private static function compileExceptionMessage(string $path, ChainAttemptNotLoadableException ...$exceptions): string
    {
        return vsprintf('Source image not resolvable "%s" using "%s" %d loaders (internal exceptions: %s).', [
            $path,
            self::compileLoaderConfigMaps(...$exceptions),
            \count($exceptions),
            self::compileLoaderErrorsList(...$exceptions),
        ]);
    }

    private static function compileLoaderConfigMaps(ChainAttemptNotLoadableException ...$exceptions): string
    {
        return self::implodeMappedExceptions(static function (ChainAttemptNotLoadableException $e): string {
            return $e->getMessage();
        }, ...$exceptions);
    }

    private static function compileLoaderErrorsList(ChainAttemptNotLoadableException ...$exceptions): string
    {
        return self::implodeMappedExceptions(static function (ChainAttemptNotLoadableException $e): string {
            return sprintf('%s=[%s]', $e->getLoaderClassName(), $e->getLoaderException());
        }, ...$exceptions);
    }

    private static function implodeMappedExceptions(\Closure $mapper, ChainAttemptNotLoadableException ...$exceptions): string
    {
        return implode(', ', array_map($mapper, $exceptions));
    }
}
