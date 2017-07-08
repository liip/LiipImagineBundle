<?php

namespace Liip\ImagineBundle\Async;

use Enqueue\Util\JSON;

class ResolveCache implements \JsonSerializable
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array|null|\string[]
     */
    private $filters;

    /**
     * @var bool
     */
    private $force;

    /**
     * @param string        $path
     * @param string[]|null $filters
     * @param bool          $force
     */
    public function __construct($path, array $filters = null, $force = false)
    {
        $this->path = $path;
        $this->filters = $filters;
        $this->force = $force;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return null|\string[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return bool
     */
    public function isForce()
    {
        return $this->force;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array('path' => $this->path, 'filters' => $this->filters, 'force' => $this->force);
    }

    /**
     * @param string $json
     *
     * @return static
     */
    public static function jsonDeserialize($json)
    {
        $data = array_replace(array('path' => null, 'filters' => null, 'force' => false), JSON::decode($json));

        if (false == $data['path']) {
            throw new \LogicException('The message does not contain "path" but it is required.');
        }

        if (false == (is_null($data['filters']) || is_array($data['filters']))) {
            throw new \LogicException('The message filters could be either null or array.');
        }

        return new static($data['path'], $data['filters'], $data['force']);
    }
}
