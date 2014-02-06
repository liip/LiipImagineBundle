# AwsS3ProxyResolver

The AwsS3ProxyResolver requires the [aws-sdk-php](https://github.com/aws/aws-sdk-php).

For configuring this Cache-Resolver see [AwsS3Resolver](aws_s3.md)

This Resolver adds the possibility to use a S3 Proxy host (or more) as Cache Resolver.
If no Proxy Domains are set, it behaves like the `AwsS3Resolver`

## Set Proxy Domains

In order to use this Resolver you must create a Service and inject some domains

``` yaml
services:
    acme.imagine.cache.resolver.amazon_s3_proxy:
        class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3ProxyResolver
        arguments:
            - "@acme.amazon_s3"
            - "%amazon_s3.bucket%"
        calls:
             - [ setProxyHosts, [ 'http://images0.domain.com', 'http://images1.domain.com','http://images2.domain.com' ] ]
        tags:
            - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3_proxy' }
```

Now your Resolver would generate following Urls "http://images0.domain.com/thumbs/article_thumb/foo.jpg" instead of "bucket.s3.awsamazoncloud.com/thumbs/article_thumb/foo.jpg"

- [Back to cache resolvers](../cache-resolvers.md)
- [Back to the index](../index.md)
