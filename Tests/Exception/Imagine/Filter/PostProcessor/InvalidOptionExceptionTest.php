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
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException
 */
class InvalidOptionExceptionTest extends TestCase
{
    public static function provideExceptionMessageData(): array
    {
        return [
            ['a foobar message', [], ''],
            ['a foobar message', ['foo' => 'bar'], 'foo="bar"'],
            ['a foobar message', ['baz' => new \stdClass()], 'baz="{}"'],
            ['a foobar message', ['foo' => 'bar', 'baz' => new \stdClass()], 'foo="bar", baz="{}"'],
            ['a foobar message', ['foo' => 'bar', 'baz' => new \stdClass(), 'int' => 100, 'array' => ['this', 'that']], 'foo="bar", baz="{}", int="100", array="["this","that"]"'],
            ['a foobar message', ['foo' => 'bar', 'baz' => new \stdClass(), 'int' => 100, 'array' => ['this' => 'that']], 'foo="bar", baz="{}", int="100", array="{"this":"that"}"'],
        ];
    }

    /**
     * @dataProvider provideExceptionMessageData
     */
    public function testExceptionMessage(string $message, array $options, string $optionsText): void
    {
        $exception = new InvalidOptionException($message, $options);

        $this->assertStringContainsString(sprintf('(%s)', $message), $exception->getMessage());
        $this->assertStringContainsString(sprintf('[%s]', $optionsText), $exception->getMessage());
    }
}
