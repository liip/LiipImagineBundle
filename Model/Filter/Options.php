<?php

namespace Liip\ImagineBundle\Model\Filter;

use Liip\ImagineBundle\Exception\Imagine\Filter\Loader\UndefinedOptionException;

class Options implements \ArrayAccess
{
    protected $options = array();

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function has($option)
    {
        return isset($this->options[$option]);
    }

    public function get($option)
    {
        if (!$this->has($option)) {
            throw new UndefinedOptionException(sprintf('The option "%s" has not been defined.', $option));
        }

        return $this->options[$option];
    }

    public function offsetExists($offset)
    {
        return isset($this->options[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->options[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Filter options are immutable.');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Filter options are immutable.');
    }
}
