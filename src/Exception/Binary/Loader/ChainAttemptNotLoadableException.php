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

use Liip\ImagineBundle\Binary\Loader\LoaderInterface;

final class ChainAttemptNotLoadableException extends NotLoadableException
{
    private string          $loaderIndex;
    private LoaderInterface $loaderClass;

    public function __construct(string $loaderIndex, LoaderInterface $loaderClass, NotLoadableException $loaderException)
    {
        $this->loaderIndex = $loaderIndex;
        $this->loaderClass = $loaderClass;

        parent::__construct($this->compileMessageTxt(), 0, $loaderException);
    }

    public function getLoaderIndex(): string
    {
        return $this->loaderIndex;
    }

    public function getLoaderClass(): LoaderInterface
    {
        return $this->loaderClass;
    }

    public function getLoaderClassName(): string
    {
        return (new \ReflectionObject($this->getLoaderClass()))->getShortName();
    }

    public function getLoaderException(): string
    {
        return $this->getPrevious()->getMessage();
    }

    private function compileMessageTxt(): string
    {
        return sprintf('%s=[%s]', $this->getLoaderClassName(), $this->getLoaderIndex());
    }
}
