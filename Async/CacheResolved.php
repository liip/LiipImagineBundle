<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Async;

use Enqueue\Util\JSON;
use Liip\ImagineBundle\Exception\LogicException;

class CacheResolved implements \JsonSerializable
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string[]
     */
    private $uris;

    /**
     * @param string[]|null $uris
     */
    public function __construct(string $path, array $uris)
    {
        $this->path = $path;
        $this->uris = $uris;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getUris(): array
    {
        return $this->uris;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return ['path' => $this->path, 'uris' => $this->uris];
    }

    public static function jsonDeserialize(string $json): self
    {
        $data = JSON::decode($json);

        if (empty($data['path'])) {
            throw new LogicException('The message does not contain "path" but it is required.');
        }

        if (empty($data['uris'])) {
            throw new LogicException('The message uris must not be empty array.');
        }

        return new static($data['path'], $data['uris']);
    }
}
