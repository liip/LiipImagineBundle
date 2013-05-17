<?php

namespace Liip\ImagineBundle\Tests\Fixtures;

use Doctrine\Common\Cache\Cache;

class MemoryCache implements Cache
{
    public $data = array();

    public function fetch($id)
    {
        return (isset($this->data[$id])) ? $this->data[$id] : false;
    }

    public function contains($id)
    {
        return isset($this->data[$id]);
    }

    public function save($id, $data, $lifeTime = 0)
    {
        $this->data[$id] = $data;

        return true;
    }

    public function delete($id)
    {
        unset($this->data[$id]);

        return true;
    }

    public function getStats()
    {
        return null;
    }
}
