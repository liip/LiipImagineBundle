<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Exception\Signer;

use Liip\ImagineBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidSignedUrlException extends BadRequestHttpException implements ExceptionInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $filter;

    /**
     * @var array
     */
    private $runtimeConfig;

    public function __construct(string $path, string $filter, array $runtimeConfig, ?\Throwable $previous = null)
    {
        $this->path = $path;
        $this->filter = $filter;
        $this->runtimeConfig = $runtimeConfig;

        parent::__construct(\sprintf(
            'Signed url does not pass the sign check for path "%s" and filter "%s" and runtime config %s',
            $path,
            $filter,
            json_encode($runtimeConfig)
        ), $previous);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    public function getRuntimeConfig(): array
    {
        return $this->runtimeConfig;
    }
}
