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
        foreach ($this->loaders as $loader) {
            try {
                return $loader->find($path);
            } catch (\Exception $loaderException) {
                // handle exception later
            }
        }

        throw new NotLoadableException(vsprintf('Source image not resolvable "%s" using "%s" loaders.', array(
            $path,
            $this->getLoaderNamesString(),
        )));
    }

    /**
     * @return string
     */
    private function getLoaderNamesString()
    {
        $names = array();
        foreach ($this->loaders as $n => $l) {
            $names[] = sprintf('%s=[%s]', $n, get_class($l));
        }

        return implode(':', $names);
    }
}
