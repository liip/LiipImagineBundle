<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Utility\OptionsResolver;

use Liip\ImagineBundle\Utility\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 * @covers \Liip\ImagineBundle\Utility\OptionsResolver\OptionsResolver
 */
class OptionsResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testOptionsResolver()
    {
        $r = static::setupOptionsResolver();
        $options = $r->resolve(array(
            'foo' => 'b',
            'bar' => 100,
        ));

        $this->assertSame('b', $options['foo']);
        $this->assertSame(100, $options['bar']);
    }

    public function testDefaultOptions()
    {
        $r = static::setupOptionsResolver();
        $options = $r->resolve(array(
            'bar' => 100,
        ));

        $this->assertSame('a', $options['foo']);
        $this->assertSame(100, $options['bar']);
    }

    public function testOptionsNormalizer()
    {
        $r = static::setupOptionsResolver();
        $options = $r->resolve(array(
            'foo' => 'c',
            'bar' => 100,
        ));

        $this->assertSame('z', $options['foo']);
        $this->assertSame(100, $options['bar']);
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function testInvalidOptions()
    {
        $r = static::setupOptionsResolver();
        $r->resolve(array(
            'does-not-exist' => 'idk',
        ));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testRequiredTypes()
    {
        $r = static::setupOptionsResolver();
        $r->resolve(array(
            'bar' => array('not', 'an', 'int'),
        ));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testAllowedValues()
    {
        $r = static::setupOptionsResolver();
        $r->resolve(array(
            'foo' => 'not-allowed',
            'bar' => 100,
        ));
    }

    /**
     * @return OptionsResolver
     */
    private static function setupOptionsResolver()
    {
        $r = new OptionsResolver();
        $r->setRequired(array('foo', 'bar'));
        $r->setAllowedValues('foo', array('a', 'b', 'c', 'z'));
        $r->setDefault('foo', 'a');
        $r->setAllowedTypes('foo', array('string'));
        $r->setAllowedTypes('bar', array('integer'));
        $r->setNormalizer('foo', function (Options $options, $value) {
            return $value === 'c' ? 'z' : $value;
        });

        return $r;
    }
}
