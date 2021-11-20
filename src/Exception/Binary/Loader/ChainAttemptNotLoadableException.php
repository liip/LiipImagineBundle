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
    private string          $configName;
    private LoaderInterface $loaderInst;

    public function __construct(string $configName, LoaderInterface $loaderInst, NotLoadableException $loaderException)
    {
        $this->configName = $configName;
        $this->loaderInst = $loaderInst;

        parent::__construct($this->compileFailureText(), 0, $loaderException);
    }

    public function getLoaderConfigName(): string
    {
        return $this->configName;
    }

    public function getLoaderObjectInst(): LoaderInterface
    {
        return $this->loaderInst;
    }

    public function getLoaderObjectName(): string
    {
        return (new \ReflectionObject($this->getLoaderObjectInst()))->getShortName();
    }

    public function getLoaderPriorError(): string
    {
        return $this->getPrevious()->getMessage();
    }

    private function compileFailureText(): string
    {
        return sprintf('%s=[%s]', $this->getLoaderObjectName(), $this->getLoaderConfigName());
    }
}
