<?php


namespace Liip\ImagineBundle\ValueObject;

class ServiceResponse
{

    protected $url;

    protected $httpStatus;

    public function __construct($url, $httpStatus = 302)
    {
        $this->url = $url;
        $this->httpStatus = $httpStatus;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }


}