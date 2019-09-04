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
     * @expectedDeprecation Instantiating "%s" without a forth argument of type "%s" is deprecated since 2.2.0 and will be required in 3.0.
     */
    public function testDeprecatedConstruction(): void
    {
        $controller = new ImagineController(
            $this->createFilterServiceMock(),
            $this->createDataManagerMock(),
            $this->createSignerInterfaceMock()
        );

        $this->assertInstanceOf(ImagineController::class, $controller);
    }

    public function testConstruction(): void
    {
        $controller = new ImagineController(
            $this->createFilterServiceMock(),
            $this->createDataManagerMock(),
            $this->createSignerInterfaceMock(),
            $this->createControllerConfigInstance()
        );

        $this->assertInstanceOf(ImagineController::class, $controller);
    }

    public static function provideRedirectResponseCodeData(): \Generator
    {
        yield from ControllerConfigTest::provideRedirectResponseCodeData();
    }

    /**
     * @dataProvider provideRedirectResponseCodeData
     */
    public function testRedirectResponseCode(int $redirectResponseCode): void
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

    public static function provideInvalidRedirectResponseCodeData(): \Generator
    {
        yield from ControllerConfigTest::provideInvalidRedirectResponseCodeData();
    }

    /**
     * @dataProvider provideInvalidRedirectResponseCodeData
     */
    public function testInvalidRedirectResponseCode(int $redirectResponseCode): void
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
