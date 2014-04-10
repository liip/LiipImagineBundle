<?php
namespace Liip\ImagineBundle\Tests\Events;

use Liip\ImagineBundle\Events\CacheResolveEvent;

/**
 * Test class for CacheResolverEvent.
 */
class CacheResolverEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Liip\ImagineBundle\Events\CacheResolveEvent
     */
    protected $event;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->event = new CacheResolveEvent('default_path', 'default_filter');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->event = null;
    }

    public function testGetPath()
    {
        $this->assertEquals('default_path', $this->event->getPath());
    }

    public function testSetPath()
    {
        $this->event->setPath('new_path');
        $this->assertEquals('new_path', $this->event->getPath());
    }

    public function testGetFilter()
    {
        $this->assertEquals('default_filter', $this->event->getFilter());
    }

    public function testSetFilter()
    {
        $this->event->setFilter('new_filter');
        $this->assertEquals('new_filter', $this->event->getFilter());
    }

    public function testGetUrl()
    {
        $this->assertNull($this->event->getUrl());
    }

    public function testSetUrl()
    {
        $this->event->setUrl('new_url');
        $this->assertEquals('new_url', $this->event->getUrl());
    }
}
