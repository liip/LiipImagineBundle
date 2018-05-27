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

use Liip\ImagineBundle\Config\Controller\ControllerConfig;
use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\Config\Controller\ControllerConfigTest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Liip\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends AbstractTest
{
    /**
     * @group legacy
     * @expectedDeprecation Instantiating "%s" without a forth argument "%s" is deprecated.
     */
    public function testDeprecatedConstruction()
    {
        $controller = new ImagineController(
            $this->createFilterServiceMock(),
            $this->createDataManagerMock(),
            $this->createSignerInterfaceMock()
        );

        $this->assertInstanceOf(ImagineController::class, $controller);
    }

    public function testConstruction()
    {
        $controller = new ImagineController(
            $this->createFilterServiceMock(),
            $this->createDataManagerMock(),
            $this->createSignerInterfaceMock(),
            $this->createControllerConfigInstance()
        );

        $this->assertInstanceOf(ImagineController::class, $controller);
    }

    /**
     * @return \Generator
     */
    public static function provideRedirectResponseCodeData(): \Generator
    {
        yield from ControllerConfigTest::provideRedirectResponseCodeData();
    }

    /**
     * @dataProvider provideRedirectResponseCodeData
     *
     * @param int $redirectResponseCode
     */
    public function testRedirectResponseCode(int $redirectResponseCode)
    {
        $controller = $this->createControllerInstance(
            $path = '/foo',
            $filter = 'filter',
            $hash = 'hash',
            $redirectResponseCode
        );

        $response = $controller->filterAction(new Request(), $path, $filter);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($redirectResponseCode, $response->getStatusCode());

        $response = $controller->filterRuntimeAction(new Request(), $hash, $path, $filter);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($redirectResponseCode, $response->getStatusCode());
    }

    /**
     * @return \Generator
     */
    public static function provideInvalidRedirectResponseCodeData(): \Generator
    {
        yield from ControllerConfigTest::provideInvalidRedirectResponseCodeData();
    }

    /**
     * @dataProvider provideInvalidRedirectResponseCodeData
     *
     * @param int $redirectResponseCode
     */
    public function testInvalidRedirectResponseCode(int $redirectResponseCode)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createControllerInstance(
            $path = '/foo',
            $filter = 'filter',
            $hash = 'hash',
            $redirectResponseCode,
            false
        );
    }

    /**
     * @param string $path
     * @param string $filter
     * @param string $hash
     * @param int    $redirectResponseCode
     * @param bool   $expectation
     *
     * @return ImagineController
     */
    private function createControllerInstance(string $path, string $filter, string $hash, int $redirectResponseCode, bool $expectation = true): ImagineController
    {
        $filterService = $this->createFilterServiceMock();
        $filterService
            ->expects($expectation ? $this->atLeastOnce() : $this->never())
            ->method('getUrlOfFilteredImage')
            ->with($path, $filter, null)
            ->willReturn(sprintf('/resolved/image%s', $path));

        $filterService
            ->expects($expectation ? $this->once() : $this->never())
            ->method('getUrlOfFilteredImageWithRuntimeFilters')
            ->with($path, $filter, [], null)
            ->willReturn(sprintf('/resolved/image%s', $path));

        $signer = $this->createSignerInterfaceMock();
        $signer
            ->expects($expectation ? $this->once() : $this->never())
            ->method('check')
            ->with($hash, $path, [])
            ->willReturn(true);

        return new ImagineController(
            $filterService,
            $this->createDataManagerMock(),
            $signer,
            new ControllerConfig($redirectResponseCode)
        );
    }
}
