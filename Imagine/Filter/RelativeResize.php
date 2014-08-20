<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Filter\FilterInterface;
use Imagine\Image\ImageInterface;

/**
 * Filter for resizing an image relative to its existing dimensions.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class RelativeResize implements FilterInterface
{
    private $method;
    private $parameter;
    private $filter;

    /**
     * Constructs a RelativeResize filter with the given method and argument.
     *
     * @param string $method    BoxInterface method
     * @param mixed  $parameter Parameter for BoxInterface method
     * @param string $filter    The filter to use for resizing, one of ImageInterface::FILTER_*
     */
    public function __construct($method, $parameter, $filter = ImageInterface::FILTER_UNDEFINED)
    {
        if (!in_array($method, array('heighten', 'increase', 'scale', 'widen'))) {
            throw new InvalidArgumentException(sprintf('Unsupported method: ', $method));
        }

        $this->method = $method;
        $this->parameter = $parameter;
        $this->filter = $filter;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(ImageInterface $image)
    {
        return $image->resize(call_user_func(array($image->getSize(), $this->method), $this->parameter), $this->filter);
    }
}
