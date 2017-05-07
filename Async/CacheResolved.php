<?php
namespace Liip\ImagineBundle\Async;

use Enqueue\Util\JSON;

class CacheResolved implements \JsonSerializable
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var \string[]
     */
    private $uris;
    
    /**
     * @param string $path
     * @param string[]|null $uris
     */
    public function __construct($path, array $uris)
    {
        $this->path = $path;
        $this->uris = $uris;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return \string[]
     */
    public function getUris()
    {
        return $this->uris;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array('path' => $this->path, 'uris' => $this->uris);
    }

    /**
     * @param string $json
     *
     * @return static
     */
    public static function jsonDeserialize($json)
    {
        $data = JSON::decode($json);

        if (empty($data['path'])) {
            throw new \LogicException('The message does not contain "path" but it is required.');
        }

        if (empty($data['uris'])) {
            throw new \LogicException('The message uris must not be empty array.');
        }

        return new static($data['path'], $data['uris']);
    }
}
