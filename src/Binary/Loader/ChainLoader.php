<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary\Loader;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

class ChainLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface[]
     */
    private $loaders;

    /**
     * @param LoaderInterface[] $loaders
     */
    public function __construct(array $loaders)
    {
        $this->loaders = array_filter($loaders, function ($loader) {
            return $loader instanceof LoaderInterface;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        $exceptions = [];

        foreach ($this->loaders as $loader) {
            try {
                return $loader->find($path);
            } catch (\Exception $e) {
                $exceptions[$e->getMessage()] = $loader;
            }
        }

        throw new NotLoadableException(self::getLoaderExceptionMessage($path, $exceptions, $this->loaders));
    }

    /**
     * @param \Exception[] $exceptions
     */
    private static function getLoaderExceptionMessage(string $path, array $exceptions, array $loaders): string
    {
        array_walk($loaders, function (LoaderInterface &$loader, string $name): void {
            $loader = sprintf('%s=[%s]', (new \ReflectionObject($loader))->getShortName(), $name);
        });

        array_walk($exceptions, function (LoaderInterface &$loader, string $message): void {
            $loader = sprintf('%s=[%s]', (new \ReflectionObject($loader))->getShortName(), $message);
        });

        return vsprintf('Source image not resolvable "%s" using "%s" %d loaders (internal exceptions: %s).', [
            $path,
            implode(', ', $loaders),
            \count($loaders),
            implode(', ', $exceptions),
        ]);
    }
}
