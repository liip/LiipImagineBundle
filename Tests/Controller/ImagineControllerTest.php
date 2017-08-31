<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Controller;

use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Liip\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends AbstractTest
{
    public function testConstruction()
    {
        new ImagineController(
            $this->createDataManagerMock(),
            $this->createFilterManagerMock(),
            $this->createCacheManagerMock(),
            $this->createSignerInterfaceMock(),
            $this->createLoggerInterfaceMock(),
            301
        );
    }

    public function testRedirectCodeIsConfigurable()
    {
        $redirectResponseCode = 307;
        $path = '/foo';
        $filter = 'filter';
        $binary = $this->createObjectMock('\Liip\ImagineBundle\Model\Binary');
        $hash = 'hash';

        $dataManager = $this->createDataManagerMock();
        $dataManager
            ->method('find')
            ->with($filter, $path)
            ->willReturn($binary);

        $filterManager = $this->createFilterManagerMock();
        $filterManager
            ->method('applyFilter')
            ->with($binary, $filter)
            ->willReturn($binary);

        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->method('resolve')
            ->willReturn($path, $filter)
            ->willReturn('/target');

        $signer = $this->createSignerInterfaceMock();
        $signer
            ->expects($this->once())
            ->method('check')
            ->with($hash, $path, array())
            ->willReturn(true);

        $controller = new ImagineController(
            $dataManager,
            $filterManager,
            $cacheManager,
            $signer,
            $this->createLoggerInterfaceMock(),
            $redirectResponseCode
        );

        $response = $controller->filterAction(new Request(), $path, $filter);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame($redirectResponseCode, $response->getStatusCode());

        $response = $controller->filterRuntimeAction(new Request(), $hash, $path, $filter);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame($redirectResponseCode, $response->getStatusCode());
    }
}
