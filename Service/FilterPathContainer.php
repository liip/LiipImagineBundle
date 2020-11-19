<?php
declare(strict_types=1);

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Service;

final class FilterPathContainer
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    /**
     * @var mixed[]
     */
    private $options;

    /**
     * @param string  $source
     * @param string  $target
     * @param mixed[] $options
     */
    public function __construct(string $source, string $target = '', array $options = [])
    {
        $this->source = $source;
        $this->target = '' !== $target ? $target : $source;
        $this->options = $options;
    }

    /**
     * @param int $quality
     *
     * @return self
     */
    public function createWebp(int $quality): self
    {
        return new self(
            $this->source,
            $this->target.'.webp',
            [
                'quality' => $quality,
                'format' => 'webp',
            ] + $this->options
        );
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
