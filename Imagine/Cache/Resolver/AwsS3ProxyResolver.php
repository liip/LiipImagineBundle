<?php


namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AwsS3ProxyResolver
 *
 * interally its the same like the AwsS3Resolver except it generate pretty proxy Urls
 * e.g. you have a proxy installed "images.website.com" which transparently points to your bucket "bucket.s3.awsamazoncloud.com"
 * this resolver would generate resolve & create s3 objects through the native s3 handling, buts gives you back the proxied url instead of the bucket path
 *
 * "images.website.com/thumbs/article_thumb/foo.jpg" instead of "bucket.s3.awsamazoncloud.com/thumbs/article_thumb/foo.jpg"
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class AwsS3ProxyResolver extends AwsS3Resolver
{
    /**
     * @var string
     */
    private $proxyDomains;

    /**
     * set the proxy host
     *
     * @param string|array $domains a valid host
     */
    public function setProxyHosts($domains)
    {
        $this->proxyDomains = $domains;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, $path, $filter)
    {
        $objectPath = $this->getObjectPath($path, $filter);

        if ($this->proxyDomains && $this->objectExists($objectPath)) {
            return new RedirectResponse($this->createProxyUrl($objectPath), 301);
        }

        return parent::resolve($request, $path, $filter);
    }

    /**
     * {@inheritDoc}
     */
    public function store(Response $response, $targetPath, $filter)
    {
        $response = parent::store($response, $targetPath, $filter);
        /** @var Response $response */
        if ($this->proxyDomains && $response->getStatusCode() == 301) {
            $response->headers->set('Location', $this->createProxyUrl($targetPath));
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getBrowserPath($path, $filter, $absolute = false)
    {
        $objectPath = $this->getObjectPath($path, $filter);

        if ($this->proxyDomains && $this->objectExists($objectPath)) {
            return $this->createProxyUrl($objectPath);
        }

        return parent::getBrowserPath($path, $filter, $absolute);
    }

    private function createProxyUrl($path)
    {
        $domain = is_array($this->proxyDomains) ? $this->proxyDomains[rand(0, count($this->proxyDomains)-1)] : $this->proxyDomains;

        return $domain . DIRECTORY_SEPARATOR . $path;
    }

}