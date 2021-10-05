<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Cache;

use Liip\ImagineBundle\Events\CacheResolveEvent;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\Signer;
use Liip\ImagineBundle\ImagineEvents;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\Fixtures\CacheManagerAwareResolver;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractTest
{
    public function testAddCacheManagerAwareResolver(): void
    {
        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );

        $resolver = $this->createCacheManagerAwareResolverMock();
        $resolver
            ->expects($this->once())
            ->method('setCacheManager')
            ->with($cacheManager);

        $cacheManager->addResolver('thumbnail', $resolver);
    }

    public function testGetBrowserPathWithoutResolver(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Could not find resolver "default" for "thumbnail" filter type');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]);

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');
    }

    public function testGetRuntimePath(): void
    {
        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );

        $rcPath = $cacheManager->getRuntimePath('image.jpg', [
            'thumbnail' => [
                'size' => [180, 180],
            ],
        ]);

        $this->assertSame('rc/ILfTutxX/image.jpg', $rcPath);
    }

    public function testDefaultResolverUsedIfNoneSetOnGetBrowserPath(): void
    {
        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->willReturn(true);
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->willReturn('http://a/path/to/an/image.png');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->exactly(2))
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]);

        $router = $this->createRouterInterfaceMock();
        $router
            ->expects($this->never())
            ->method('generate');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertSame('http://a/path/to/an/image.png', $actualBrowserPath);
    }

    public function testDefaultResolverUsedIfNoneSetOnGetBrowserPathWithWebPGenerate(): void
    {
        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->never())
            ->method('isStored');
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->never())
            ->method('get');

        $router = $this->createRouterInterfaceMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/media/cache/thumbnail/cats.jpeg');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock(),
            null,
            true
        );
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertSame('/media/cache/thumbnail/cats.jpeg', $actualBrowserPath);
    }

    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPath(): void
    {
        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->willReturn(false);
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]);

        $router = $this->createRouterInterfaceMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/media/cache/thumbnail/cats.jpeg');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertSame('/media/cache/thumbnail/cats.jpeg', $actualBrowserPath);
    }

    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPathWithRuntimeConfig(): void
    {
        $runtimeConfig = [
            'thumbnail' => [
                'size' => [100, 100],
            ],
        ];

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('rc/VhOzTGRB/cats.jpeg', 'thumbnail')
            ->willReturn(false);
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]);

        $router = $this->createRouterInterfaceMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', $runtimeConfig);

        $this->assertSame('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg', $actualBrowserPath);
    }

    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPathWithRuntimeConfigWithWebPGenerate(): void
    {
        $runtimeConfig = [
            'thumbnail' => [
                'size' => [100, 100],
            ],
        ];

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->never())
            ->method('isStored');
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->never())
            ->method('get');

        $router = $this->createRouterInterfaceMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock(),
            null,
            true
        );
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', $runtimeConfig);

        $this->assertSame('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg', $actualBrowserPath);
    }

    /**
     * @dataProvider invalidPathProvider
     *
     * @param string $path
     */
    public function testResolveInvalidPath($path): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );

        $cacheManager->resolve($path, 'thumbnail');
    }

    public function testThrowsIfConcreteResolverNotExists(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Could not find resolver "default" for "thumbnail" filter type');

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );

        $this->assertFalse($cacheManager->resolve('cats.jpeg', 'thumbnail'));
    }

    public function testFallbackToDefaultResolver(): void
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->willReturn('/thumbs/cats.jpeg');
        $resolver
            ->expects($this->once())
            ->method('store')
            ->with($binary, '/thumbs/cats.jpeg', 'thumbnail');
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(['/thumbs/cats.jpeg'], ['thumbnail'])
            ->willReturn(true);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->exactly(3))
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]);

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver('default', $resolver);
        $cacheManager->setCacheWarmer($this->createCacheWarmerMock());

        // Resolve fallback to default resolver
        $this->assertSame('/thumbs/cats.jpeg', $cacheManager->resolve('cats.jpeg', 'thumbnail'));

        $cacheManager->store($binary, '/thumbs/cats.jpeg', 'thumbnail');

        // Remove fallback to default resolver
        $cacheManager->remove('/thumbs/cats.jpeg', 'thumbnail');
    }

    public function testGenerateUrl(): void
    {
        $path = 'thePath';
        $expectedUrl = 'theUrl';

        $routerMock = $this->createRouterInterfaceMock();
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with(
                'liip_imagine_filter',
                [
                    'path' => $path,
                    'filter' => 'thumbnail',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($expectedUrl);

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $routerMock,
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );

        $this->assertSame(
            $expectedUrl,
            $cacheManager->generateUrl($path, 'thumbnail')
        );
    }

    public function testRemoveCacheForPathAndFilterOnRemove(): void
    {
        $expectedPath = 'thePath';
        $expectedFilter = 'theFilter';

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilter]);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            });

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver($expectedFilter, $resolver);
        $cacheManager->setCacheWarmer($this->createCacheWarmerMock());
        $cacheManager->remove($expectedPath, $expectedFilter);
    }

    public function testRemoveCacheForPathAndSomeFiltersOnRemove(): void
    {
        $expectedPath = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createCacheResolverInterfaceMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterOne]);

        $resolverTwo = $this->createCacheResolverInterfaceMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterTwo]);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            });

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->setCacheWarmer($this->createCacheWarmerMock());
        $cacheManager->remove($expectedPath, [$expectedFilterOne, $expectedFilterTwo]);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove(): void
    {
        $expectedPathOne = 'thePathOne';
        $expectedPathTwo = 'thePathTwo';
        $expectedFilter = 'theFilter';

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(
                [$expectedPathOne, $expectedPathTwo],
                [$expectedFilter]
            );

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            });

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver($expectedFilter, $resolver);
        $cacheManager->setCacheWarmer($this->createCacheWarmerMock());
        $cacheManager->remove([$expectedPathOne, $expectedPathTwo], $expectedFilter);
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove(): void
    {
        $expectedPathOne = 'thePath';
        $expectedPathTwo = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createCacheResolverInterfaceMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPathOne, $expectedPathTwo], [$expectedFilterOne]);

        $resolverTwo = $this->createCacheResolverInterfaceMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPathOne, $expectedPathTwo], [$expectedFilterTwo]);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            });

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->setCacheWarmer($this->createCacheWarmerMock());
        $cacheManager->remove(
            [$expectedPathOne, $expectedPathTwo],
            [$expectedFilterOne, $expectedFilterTwo]
        );
    }

    public function testRemoveCacheForAllFiltersOnRemove(): void
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createCacheResolverInterfaceMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterOne]);

        $resolverTwo = $this->createCacheResolverInterfaceMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterTwo]);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            });
        $config
            ->expects($this->once())
            ->method('all')
            ->willReturn([
                $expectedFilterOne => [],
                $expectedFilterTwo => [],
            ]);

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->setCacheWarmer($this->createCacheWarmerMock());
        $cacheManager->remove();
    }

    public function testRemoveCacheForPathAndAllFiltersOnRemove(): void
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';
        $expectedPath = 'thePath';

        $resolverOne = $this->createCacheResolverInterfaceMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterOne]);

        $resolverTwo = $this->createCacheResolverInterfaceMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterTwo]);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            });
        $config
            ->expects($this->once())
            ->method('all')
            ->willReturn([
                $expectedFilterOne => [],
                $expectedFilterTwo => [],
            ]);

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->setCacheWarmer($this->createCacheWarmerMock());
        $cacheManager->remove($expectedPath);
    }

    public function testAggregateFiltersByResolverOnRemove(): void
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterOne, $expectedFilterTwo]);

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            });

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $this->createEventDispatcherInterfaceMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolver);
        $cacheManager->addResolver($expectedFilterTwo, $resolver);
        $cacheManager->setCacheWarmer($this->createCacheWarmerMock());
        $cacheManager->remove(null, [$expectedFilterOne, $expectedFilterTwo]);
    }

    public function testShouldDispatchCachePreResolveEvent(): void
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(...$this->getDispatcherArgumentsWithBC($dispatcher, [
                new CacheResolveEvent('cats.jpg', 'thumbnail'),
                ImagineEvents::PRE_RESOLVE,
            ]));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $this->createCacheResolverInterfaceMock());
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldDispatchCachePostResolveEvent(): void
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(...$this->getDispatcherArgumentsWithBC($dispatcher, [
                new CacheResolveEvent('cats.jpg', 'thumbnail'),
                ImagineEvents::POST_RESOLVE,
            ]));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $this->createCacheResolverInterfaceMock());
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldAllowToPassChangedDataFromPreResolveEventToResolver(): void
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(...$this->getDispatcherArgumentsWithBC($dispatcher, [
                $this->isInstanceOf(CacheResolveEvent::class),
                ImagineEvents::PRE_RESOLVE,
            ]))
            ->willReturnCallback($this->getDispatcherCallbackWithBC($dispatcher, function ($event) {
                $event->setPath('changed_path');
                $event->setFilter('changed_filter');
            }));

        $resolver = $this->createCacheResolverInterfaceMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('changed_path', 'changed_filter');

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $resolver);
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldAllowToGetResolverByFilterChangedInPreResolveEvent(): void
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->willReturnCallback($this->getDispatcherCallbackWithBC($dispatcher, function ($event) {
                $event->setFilter('thumbnail');
            }));

        $cacheManager = $this
            ->getMockBuilder(CacheManager::class)
            ->setMethods(['getResolver'])
            ->setConstructorArgs([
                $this->createFilterConfigurationMock(),
                $this->createRouterInterfaceMock(),
                new Signer('secret'),
                $dispatcher,
            ])->getMock();

        $cacheManager
            ->expects($this->once())
            ->method('getResolver')
            ->with('thumbnail')
            ->willReturn($this->createCacheResolverInterfaceMock());

        $cacheManager->resolve('cats.jpg', 'default');
    }

    public function testShouldAllowToPassChangedDataFromPreResolveEventToPostResolveEvent(): void
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(...$this->getDispatcherArgumentsWithBC($dispatcher, [
                $this->isInstanceOf(CacheResolveEvent::class),
                ImagineEvents::PRE_RESOLVE,
            ]))
            ->willReturnCallback($this->getDispatcherCallbackWithBC($dispatcher, function (CacheResolveEvent $event) {
                $event->setPath('changed_path');
                $event->setFilter('changed_filter');
            }));

        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(...$this->getDispatcherArgumentsWithBC($dispatcher, [$this->logicalAnd(
                $this->isInstanceOf(CacheResolveEvent::class),
                $this->callback(function (CacheResolveEvent $event) {
                    return 'changed_filter' === $event->getFilter() && 'changed_path' === $event->getPath();
                })
            ), ImagineEvents::POST_RESOLVE]));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $this->createCacheResolverInterfaceMock());
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldReturnUrlChangedInPostResolveEvent(): void
    {
        $dispatcher = $this->createEventDispatcherInterfaceMock();
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(...$this->getDispatcherArgumentsWithBC($dispatcher, [
                $this->isInstanceOf(CacheResolveEvent::class),
                ImagineEvents::POST_RESOLVE,
            ]))
            ->willReturnCallback($this->getDispatcherCallbackWithBC($dispatcher, function ($event) {
                $event->setUrl('changed_url');
            }));

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterInterfaceMock(),
            new Signer('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $this->createCacheResolverInterfaceMock());

        $this->assertSame('changed_url', $cacheManager->resolve('cats.jpg', 'thumbnail'));
    }

    /**
     * @return MockObject|ResolverInterface
     */
    private function createCacheManagerAwareResolverMock()
    {
        return $this
            ->getMockBuilder(CacheManagerAwareResolver::class)
            ->getMock();
    }

    /**
     * BC Layer for Symfony < 4.3
     */
    private function getDispatcherCallbackWithBC(EventDispatcherInterface $dispatcher, callable $callable): callable
    {
        return function ($event, $name) use ($dispatcher, $callable) {
            if ($dispatcher instanceof ContractsEventDispatcherInterface) {
                $callable($event, $name);
            } else {
                $callable($name, $event);
            }

            return $event;
        };
    }

    /**
     * BC Layer for Symfony < 4.3
     */
    private function getDispatcherArgumentsWithBC(EventDispatcherInterface $dispatcher, array $arguments): array
    {
        if (!$dispatcher instanceof ContractsEventDispatcherInterface) {
            $arguments = [$arguments[1], $arguments[0]];
        }

        return $arguments;
    }
}
