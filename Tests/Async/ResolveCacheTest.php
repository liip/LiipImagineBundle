<?php
namespace Liip\ImagineBundle\Tests\Async;

use Liip\ImagineBundle\Async\ResolveCache;

class ResolveCacheTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        if (false == getenv('WITH_ENQUEUE')) {
            self::markTestSkipped('The tests are run without enqueue integration. Skip them');
        }
    }

    public function testCouldBeJsonSerializedWithoutFiltersAndForce()
    {
        $message = new ResolveCache('thePath');

        $this->assertEquals('{"path":"thePath","filters":null,"force":false}', json_encode($message));
    }

    public function testCouldBeJsonSerializedWithFilters()
    {
        $message = new ResolveCache('thePath', array('fooFilter', 'barFilter'));

        $this->assertEquals('{"path":"thePath","filters":["fooFilter","barFilter"],"force":false}', json_encode($message));
    }

    public function testCouldBeJsonSerializedWithFiltersAndForce()
    {
        $message = new ResolveCache('thePath', array('fooFilter', 'barFilter'), true);

        $this->assertEquals('{"path":"thePath","filters":["fooFilter","barFilter"],"force":true}', json_encode($message));
    }

    public function testCouldBeJsonDeSerializedWithoutFiltersAndForce()
    {
        $message = ResolveCache::jsonDeserialize('{"path":"thePath","filters":null,"force":false}');

        $this->assertInstanceOf('Liip\ImagineBundle\Async\ResolveCache', $message);
        $this->assertEquals('thePath', $message->getPath());
        $this->assertNull($message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testCouldBeJsonDeSerializedWithFilters()
    {
        $message = ResolveCache::jsonDeserialize('{"path":"thePath","filters":["fooFilter","barFilter"],"force":false}');

        $this->assertInstanceOf('Liip\ImagineBundle\Async\ResolveCache', $message);
        $this->assertEquals('thePath', $message->getPath());
        $this->assertEquals(array('fooFilter', 'barFilter'), $message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testCouldBeJsonDeSerializedWithFiltersAndForce()
    {
        $message = ResolveCache::jsonDeserialize('{"path":"thePath","filters":["fooFilter","barFilter"],"force":true}');

        $this->assertInstanceOf('Liip\ImagineBundle\Async\ResolveCache', $message);
        $this->assertEquals('thePath', $message->getPath());
        $this->assertEquals(array('fooFilter', 'barFilter'), $message->getFilters());
        $this->assertTrue($message->isForce());
    }

    public function testCouldBeJsonDeSerializedWithOnlyPath()
    {
        $message = ResolveCache::jsonDeserialize('{"path":"thePath"}');

        $this->assertInstanceOf('Liip\ImagineBundle\Async\ResolveCache', $message);
        $this->assertEquals('thePath', $message->getPath());
        $this->assertNull($message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testThrowIfMessageMissingPathOnJsonDeserialize()
    {
        $this->setExpectedException('LogicException', 'The message does not contain "path" but it is required.');
        ResolveCache::jsonDeserialize('{}');
    }

    public function testThrowIfMessageContainsNotSupportedFilters()
    {
        $this->setExpectedException('LogicException', 'The message filters could be either null or array.');
        ResolveCache::jsonDeserialize('{"path": "aPath", "filters": "stringFilterIsNotAllowed"}');
    }
}