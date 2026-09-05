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

use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Config\Controller\ControllerConfig;
use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\Config\Controller\ControllerConfigTest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers \Liip\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends AbstractTest
{
    /**
     * @group legacy
     *
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
            '/foo',
            'filter',
            'hash',
            $redirectResponseCode,
            false
        );
    }

    public static function provideGenerationFailureData(): \Generator
    {
        yield 'non existing filter' => [
            new NonExistingFilterException('Filter not found'),
            NotFoundHttpException::class,
            'Requested non-existing filter "filter"',
        ];

        yield 'image can not be generated' => [
            new RuntimeException('Imagine gave up'),
            \RuntimeException::class,
            'Unable to create image for path "/foo" and filter "filter". Message was "Imagine gave up"',
        ];
    }

    /**
     * @dataProvider provideGenerationFailureData
     */
    public function testRedirectsToDefaultImageWhenDebugIsDisabled(\Exception $exception): void
    {
        $controller = $this->createFailingControllerInstance($exception, '/default/image.png', false);

        $response = $controller->filterAction(new Request(), '/foo', 'filter');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/default/image.png', $response->getTargetUrl());
    }

    /**
     * @dataProvider provideGenerationFailureData
     */
    public function testRuntimeActionRedirectsToDefaultImageWhenDebugIsDisabled(\Exception $exception): void
    {
        $controller = $this->createFailingControllerInstance($exception, '/default/image.png', false, true);

        $response = $controller->filterRuntimeAction(new Request(), 'hash', '/foo', 'filter');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/default/image.png', $response->getTargetUrl());
    }

    /**
     * @dataProvider provideGenerationFailureData
     */
    public function testThrowsWhenDebugIsEnabled(\Exception $exception, string $expectedException, string $expectedMessage): void
    {
        $controller = $this->createFailingControllerInstance($exception, '/default/image.png', true);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $controller->filterAction(new Request(), '/foo', 'filter');
    }

    /**
     * @dataProvider provideGenerationFailureData
     */
    public function testThrowsWithoutDefaultImageEvenWhenDebugIsDisabled(\Exception $exception, string $expectedException, string $expectedMessage): void
    {
        $controller = $this->createFailingControllerInstance($exception, null, false);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $controller->filterAction(new Request(), '/foo', 'filter');
    }

    public function testLogsTheExceptionItReplacedByTheDefaultImage(): void
    {
        $exception = new RuntimeException('Imagine gave up');

        $logger = $this->createObjectMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                'Failed to create image for filter "filter", falling back to the default image. Message was "Imagine gave up"',
                ['exception' => $exception]
            );

        $controller = $this->createFailingControllerInstance($exception, '/default/image.png', false, false, $logger);

        $controller->filterAction(new Request(), '/foo', 'filter');
    }

    private function createFailingControllerInstance(\Exception $exception, ?string $defaultImageUrl, bool $debug, bool $runtimeFilters = false, ?LoggerInterface $logger = null): ImagineController
    {
        $filterService = $this->createFilterServiceMock();
        $filterService
            ->expects($this->once())
            ->method($runtimeFilters ? 'getUrlOfFilteredImageWithRuntimeFilters' : 'getUrlOfFilteredImage')
            ->willThrowException($exception);

        $dataManager = $this->createDataManagerMock();
        $dataManager
            ->method('getDefaultImageUrl')
            ->with('filter')
            ->willReturn($defaultImageUrl);

        $signer = $this->createSignerInterfaceMock();
        $signer
            ->method('check')
            ->willReturn(true);

        return new ImagineController(
            $filterService,
            $dataManager,
            $signer,
            new ControllerConfig(301, $debug),
            $logger
        );
    }

    private function createControllerInstance(string $path, string $filter, string $hash, int $redirectResponseCode, bool $expectation = true): ImagineController
    {
        $filterService = $this->createFilterServiceMock();
        $filterService
            ->expects($expectation ? $this->atLeastOnce() : $this->never())
            ->method('getUrlOfFilteredImage')
            ->with($path, $filter, null)
            ->willReturn(\sprintf('/resolved/image%s', $path));

        $filterService
            ->expects($expectation ? $this->once() : $this->never())
            ->method('getUrlOfFilteredImageWithRuntimeFilters')
            ->with($path, $filter, [], null)
            ->willReturn(\sprintf('/resolved/image%s', $path));

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
