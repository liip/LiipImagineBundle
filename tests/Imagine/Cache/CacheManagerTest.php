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
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\ImagineEvents;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\Fixtures\CacheManagerAwareResolver;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractTest
{
    public function testAddCacheManagerAwareResolver(): void
    {
        $cacheManager = new CacheManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );

        $resolver = $this->createMock(CacheManagerAwareResolver::class);
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

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');
    }

    public function testGetRuntimePath(): void
    {
        $cacheManager = new CacheManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
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
        $resolver = $this->createMock(ResolverInterface::class);
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

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->exactly(2))
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->never())
            ->method('generate');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertSame('http://a/path/to/an/image.png', $actualBrowserPath);
    }

    public function testDefaultResolverUsedIfNoneSetOnGetBrowserPathWithWebPGenerate(): void
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->never())
            ->method('isStored');
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->never())
            ->method('get');

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/media/cache/thumbnail/cats.jpeg');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherMock(),
            null,
            true
        );
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertSame('/media/cache/thumbnail/cats.jpeg', $actualBrowserPath);
    }

    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPath(): void
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->willReturn(false);
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/media/cache/thumbnail/cats.jpeg');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherMock()
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

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('rc/VhOzTGRB/cats.jpeg', 'thumbnail')
            ->willReturn(false);
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->willReturn([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherMock()
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

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->never())
            ->method('isStored');
        $resolver
            ->expects($this->never())
            ->method('resolve');

        $config = $this->createMock(FilterConfiguration::class);
        $config
            ->expects($this->never())
            ->method('get');

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg');

        $cacheManager = new CacheManager(
            $config,
            $router,
            new Signer('secret'),
            $this->createEventDispatcherMock(),
            null,
            true
        );
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', $runtimeConfig);

        $this->assertSame('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg', $actualBrowserPath);
    }

    /**
     * @return string[]
     */
    public function invalidPathProvider(): array
    {
        return [
            [$this->fixturesPath.'/assets/../../foobar.png'],
            [$this->fixturesPath.'/assets/some_folder/../foobar.png'],
            ['../../outside/foobar.jpg'],
        ];
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testResolveInvalidPath(string $path): void
    {
        $cacheManager = new CacheManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );

        $this->expectException(NotFoundHttpException::class);

        $cacheManager->resolve($path, 'thumbnail');
    }

    public function testThrowsIfConcreteResolverNotExists(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Could not find resolver "default" for "thumbnail" filter type');

        $cacheManager = new CacheManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );

        $this->assertFalse($cacheManager->resolve('cats.jpeg', 'thumbnail'));
    }

    public function testFallbackToDefaultResolver(): void
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $resolver = $this->createMock(ResolverInterface::class);
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
            ->with(['/thumbs/cats.jpeg'], ['thumbnail']);

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver('default', $resolver);

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

        $routerMock = $this->createMock(RouterInterface::class);
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
            $this->createMock(FilterConfiguration::class),
            $routerMock,
            new Signer('secret'),
            $this->createEventDispatcherMock()
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

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilter]);

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver($expectedFilter, $resolver);
        $cacheManager->remove($expectedPath, $expectedFilter);
    }

    public function testRemoveCacheForPathAndSomeFiltersOnRemove(): void
    {
        $expectedPath = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createMock(ResolverInterface::class);
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterOne]);

        $resolverTwo = $this->createMock(ResolverInterface::class);
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterTwo]);

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->remove($expectedPath, [$expectedFilterOne, $expectedFilterTwo]);
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove(): void
    {
        $expectedPathOne = 'thePathOne';
        $expectedPathTwo = 'thePathTwo';
        $expectedFilter = 'theFilter';

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(
                [$expectedPathOne, $expectedPathTwo],
                [$expectedFilter]
            );

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver($expectedFilter, $resolver);
        $cacheManager->remove([$expectedPathOne, $expectedPathTwo], $expectedFilter);
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove(): void
    {
        $expectedPathOne = 'thePath';
        $expectedPathTwo = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createMock(ResolverInterface::class);
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPathOne, $expectedPathTwo], [$expectedFilterOne]);

        $resolverTwo = $this->createMock(ResolverInterface::class);
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPathOne, $expectedPathTwo], [$expectedFilterTwo]);

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->remove(
            [$expectedPathOne, $expectedPathTwo],
            [$expectedFilterOne, $expectedFilterTwo]
        );
    }

    public function testRemoveCacheForAllFiltersOnRemove(): void
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createMock(ResolverInterface::class);
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterOne]);

        $resolverTwo = $this->createMock(ResolverInterface::class);
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterTwo]);

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->remove();
    }

    public function testRemoveCacheForPathAndAllFiltersOnRemove(): void
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';
        $expectedPath = 'thePath';

        $resolverOne = $this->createMock(ResolverInterface::class);
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterOne]);

        $resolverTwo = $this->createMock(ResolverInterface::class);
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterTwo]);

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);
        $cacheManager->remove($expectedPath);
    }

    public function testAggregateFiltersByResolverOnRemove(): void
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterOne, $expectedFilterTwo]);

        $config = $this->createMock(FilterConfiguration::class);
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
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver($expectedFilterOne, $resolver);
        $cacheManager->addResolver($expectedFilterTwo, $resolver);
        $cacheManager->remove(null, [$expectedFilterOne, $expectedFilterTwo]);
    }

    public function testShouldDispatchCacheResolveEvents(): void
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    new CacheResolveEvent('cats.jpg', 'thumbnail'),
                    ImagineEvents::PRE_RESOLVE,
                ],
                [
                    new CacheResolveEvent('cats.jpg', 'thumbnail'),
                    ImagineEvents::POST_RESOLVE,
                ]
            );

        $cacheManager = new CacheManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $this->createMock(ResolverInterface::class));
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldAllowToPassChangedDataFromPreResolveEventToResolver(): void
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->isInstanceOf(CacheResolveEvent::class),
                    ImagineEvents::PRE_RESOLVE,
                ],
                [
                    $this->isInstanceOf(CacheResolveEvent::class),
                    ImagineEvents::POST_RESOLVE,
                ]
            )
            ->willReturnCallback(function (CacheResolveEvent $event, string $eventName) {
                if (ImagineEvents::PRE_RESOLVE !== $eventName) {
                    return;
                }
                $event->setPath('changed_path');
                $event->setFilter('changed_filter');
            });

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('changed_path', 'changed_filter');

        $cacheManager = new CacheManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $resolver);
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldAllowToGetResolverByFilterChangedInPreResolveEvent(): void
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->method('dispatch')
            ->willReturnCallback(function (CacheResolveEvent $event, string $eventName) {
                if (ImagineEvents::PRE_RESOLVE !== $eventName) {
                    return;
                }
                $event->setFilter('thumbnail');
            });

        $cacheManager = $this
            ->getMockBuilder(CacheManager::class)
            ->setMethods(['getResolver'])
            ->setConstructorArgs([
                $this->createMock(FilterConfiguration::class),
                $this->createMock(RouterInterface::class),
                new Signer('secret'),
                $dispatcher,
            ])->getMock();

        $cacheManager
            ->expects($this->once())
            ->method('getResolver')
            ->with('thumbnail')
            ->willReturn($this->createMock(ResolverInterface::class));

        $cacheManager->resolve('cats.jpg', 'default');
    }

    public function testShouldAllowToPassChangedDataFromPreResolveEventToPostResolveEvent(): void
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->isInstanceOf(CacheResolveEvent::class),
                    ImagineEvents::PRE_RESOLVE,
                ],
                [
                    $this->logicalAnd(
                        $this->isInstanceOf(CacheResolveEvent::class),
                        $this->callback(function (CacheResolveEvent $event) {
                            return 'changed_filter' === $event->getFilter() && 'changed_path' === $event->getPath();
                        })
                    ),
                    ImagineEvents::POST_RESOLVE,
                ]
            )
            ->willReturnCallback(function (CacheResolveEvent $event, string $eventName) {
                if (ImagineEvents::PRE_RESOLVE !== $eventName) {
                    return;
                }
                $event->setPath('changed_path');
                $event->setFilter('changed_filter');
            });

        $cacheManager = new CacheManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $dispatcher
        );

        $cacheManager->addResolver('default', $this->createMock(ResolverInterface::class));
        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    public function testShouldReturnUrlChangedInPostResolveEvent(): void
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->isInstanceOf(CacheResolveEvent::class),
                    ImagineEvents::PRE_RESOLVE,
                ],
                [
                    $this->isInstanceOf(CacheResolveEvent::class),
                    ImagineEvents::POST_RESOLVE,
                ]
            )
            ->willReturnCallback(function (CacheResolveEvent $event, string $eventName) {
                if (ImagineEvents::POST_RESOLVE !== $eventName) {
                    return;
                }
                $event->setUrl('changed_url');
            });

        $cacheManager = new CacheManager(
            $this->createMock(FilterConfiguration::class),
            $this->createMock(RouterInterface::class),
            new Signer('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $this->createMock(ResolverInterface::class));

        $this->assertSame('changed_url', $cacheManager->resolve('cats.jpg', 'thumbnail'));
    }

    private function createEventDispatcherMock(): EventDispatcherInterface&MockObject
    {
        $mock = $this->createMock(EventDispatcherInterface::class);
        $mock
            ->method('dispatch')
            ->willReturn($this);

        return $mock;
    }
}
