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
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var ExtensionGuesserInterface
     */
    protected $extensionGuesser;

    /**
     * @var string
     */
    protected $dataRoots;

    /**
     * @param MimeTypeGuesserInterface  $mimeTypeGuesser
     * @param ExtensionGuesserInterface $extensionGuesser
     * @param string[]                  $dataRoots
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeTypeGuesser,
        ExtensionGuesserInterface $extensionGuesser,
        $dataRoots
    ) {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->extensionGuesser = $extensionGuesser;

        $this->dataRoots = array_map(function ($root) {
            if (!empty($root) && false !== $realRoot = realpath($root)) {
                return $realRoot;
            }

            throw new InvalidArgumentException(sprintf('Root image path not resolvable "%s"', $root));
        }, (array) $dataRoots);

        if (count($this->dataRoots) === 0) {
            throw new InvalidArgumentException('One or more data root paths must be specified.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        $path = $this->absolutePathRestrict($this->absolutePathLocate($path));
        $mime = $this->mimeTypeGuesser->guess($path);

        return new FileBinary($path, $mime, $this->extensionGuesser->guess($mime));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function absolutePathLocate($path)
    {
        foreach ($this->dataRoots as $root) {
            if (false !== $realPath = realpath($root.DIRECTORY_SEPARATOR.$path)) {
                return $realPath;
            }
        }

        throw new NotLoadableException(sprintf('Source image not resolvable "%s" in root path(s) "%s"',
            $path, implode(':', $this->dataRoots)));
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    private function absolutePathRestrict($path)
    {
        foreach ($this->dataRoots as $root) {
            if (0 === strpos($path, $root)) {
                return $path;
            }
        }

        throw new NotLoadableException(sprintf('Source image invalid "%s" as it is outside of the defined root path(s) "%s"',
            $path, implode(':', $this->dataRoots)));
    }
}
