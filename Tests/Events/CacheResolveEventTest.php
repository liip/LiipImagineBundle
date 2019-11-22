<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Events;

use Liip\ImagineBundle\Events\CacheResolveEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Events\CacheResolveEvent
 */
class CacheResolveEventTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testShouldAllowSetPathInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');

        $this->assertSame('default_path', $event->getPath());
    }

    public function testShouldAllowSetPathByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');
        $event->setPath('new_path');

        $this->assertSame('new_path', $event->getPath());
    }

    public function testShouldAllowGetPathWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');

        $this->assertSame('default_path', $event->getPath());
    }

    public function testShouldAllowGetPathWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');
        $event->setPath('new_path');

        $this->assertSame('new_path', $event->getPath());
    }

    public function testShouldAllowSetFilterInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');

        $this->assertSame('default_filter', $event->getFilter());
    }

    public function testShouldAllowSetFilterByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');
        $event->setFilter('new_filter');

        $this->assertSame('new_filter', $event->getFilter());
    }

    public function testShouldAllowGetFilterWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');

        $this->assertSame('default_filter', $event->getFilter());
    }

    public function testShouldAllowGetFilterWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');
        $event->setFilter('new_filter');

        $this->assertSame('new_filter', $event->getFilter());
    }

    public function testShouldAllowSetUrlInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter', 'default_url');

        $this->assertSame('default_url', $event->getUrl());
    }

    public function testShouldAllowSetUrlByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');
        $event->setUrl('new_url');

        $this->assertSame('new_url', $event->getUrl());
    }

    public function testShouldAllowGetUrlWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter', 'default_url');

        $this->assertSame('default_url', $event->getUrl());
    }

    public function testShouldAllowGetUrlWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter');
        $event->setUrl('new_url');

        $this->assertSame('new_url', $event->getUrl());
    }
}
