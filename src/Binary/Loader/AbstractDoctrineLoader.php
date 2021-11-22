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

use Doctrine\Persistence\ObjectManager;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

abstract class AbstractDoctrineLoader implements LoaderInterface
{
    protected ObjectManager $manager;

    /**
     * @phpstan-var class-string
     */
    protected string $modelClass;

    /**
     * @phpstan-param class-string $modelClass
     */
    public function __construct(ObjectManager $manager, string $modelClass)
    {
        $this->manager = $manager;
        $this->modelClass = $modelClass;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        $image = $this->manager->find($this->modelClass, $this->mapPathToId($path));

        if (!$image) {
            // try to find the image without extension
            $info = pathinfo($path);
            $name = $info['dirname'].'/'.$info['filename'];

            $image = $this->manager->find($this->modelClass, $this->mapPathToId($name));
        }

        if (!$image) {
            throw new NotLoadableException(sprintf('Source image was not found with id "%s"', $path));
        }

        return stream_get_contents($this->getStreamFromImage($image));
    }

    /**
     * Map the requested path (ie. subpath in the URL) to an id that can be used to lookup the image in the Doctrine store.
     *
     * @return string|int
     */
    abstract protected function mapPathToId(string $path);

    /**
     * Return a stream resource from the Doctrine entity/document with the image content.
     *
     * @return resource
     */
    abstract protected function getStreamFromImage(object $image);
}
