<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Exception\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException;

/**
 * @covers \Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException
 */
class InvalidOptionExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array[]
     */
    public static function provideExceptionMessageData()
    {
        return array(
            array('a foobar message', array(), ''),
            array('a foobar message', array('foo' => 'bar'), 'foo="bar"'),
            array('a foobar message', array('baz' => new \stdClass()), 'baz="stdClass::__set_state(array())"'),
            array('a foobar message', array('foo' => 'bar', 'baz' => new \stdClass()), 'foo="bar", baz="stdClass::__set_state(array())"'),
            array('a foobar message', array('foo' => 'bar', 'baz' => new \stdClass(), 'int' => 100, 'array' => array('this', 'that')), 'foo="bar", baz="stdClass::__set_state(array())", int="100", array="["this","that"]"'),
            array('a foobar message', array('foo' => 'bar', 'baz' => new \stdClass(), 'int' => 100, 'array' => array('this' => 'that')), 'foo="bar", baz="stdClass::__set_state(array())", int="100", array="{"this":"that"}"'),
        );
    }

    /**
     * @dataProvider provideExceptionMessageData
     *
     * @param string $message
     * @param array  $options
     * @param string $optionsText
     */
    public function testExceptionMessage($message, array $options, $optionsText)
    {
        $exception = new InvalidOptionException($message, $options);

        $this->assertContains(sprintf('(%s)', $message), $exception->getMessage());
        $this->assertContains(sprintf('[%s]', $optionsText), $exception->getMessage());
    }
}
