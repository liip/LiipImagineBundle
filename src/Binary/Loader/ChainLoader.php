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

use Liip\ImagineBundle\Exception\Binary\Loader\ChainAttemptNotLoadableException;
use Liip\ImagineBundle\Exception\Binary\Loader\ChainNotLoadableException;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

class ChainLoader implements LoaderInterface
{
    /**
     * @var array<string, LoaderInterface>
     */
    private array $loaders;

    /**
     * @param array<string, LoaderInterface> $loaders
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

        foreach ($this->loaders as $configName => $loader) {
            try {
                return $loader->find($path);
            } catch (NotLoadableException $loaderException) {
                $exceptions[] = new ChainAttemptNotLoadableException($configName, $loader, $loaderException);
            }
        }

        throw new ChainNotLoadableException($path, ...$exceptions);
    }
}
