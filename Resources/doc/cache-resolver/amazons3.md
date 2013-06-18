# AmazonS3Resolver

The AmazonS3Resolver requires the [aws-sdk-php](https://github.com/amazonwebservices/aws-sdk-for-php).

You can add the SDK by adding those lines to your `deps` file.

``` ini
[aws-sdk]
    git=git://github.com/amazonwebservices/aws-sdk-for-php.git
```

Afterwards, you only need to configure some information regarding your AWS account and the bucket.

``` yaml
parameters:
    amazon_s3.key: 'your-aws-key'
    amazon_s3.secret: 'your-aws-secret'
    amazon_s3.bucket: 'your-bucket.example.com'
```

Now you can set up the services required:

``` yaml
services:
    acme.amazon_s3:
        class: AmazonS3
        arguments:
            -
                key: %amazon_s3.key%
                secret: %amazon_s3.secret%
                # more S3 specific options, see \AmazonS3::__construct()

    acme.imagine.cache.resolver.amazon_s3:
        class: Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
        arguments:
            - "@acme.amazon_s3"
            - "%amazon_s3.bucket%"
        tags:
            - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }
```

Now you are ready to use the `AmazonS3Resolver` by configuring the bundle.
The following example will configure the resolver is default.

``` yaml
liip_imagine:
    cache: 'amazon_s3'
```

If you want to use other buckets for other images, simply alter the parameter names and create additional services!

## Object URL Options

In order to make use of the object URL options, you can simply add a call to the service, to alter those options you need.

``` yaml
services:
    acme.imagine.cache.resolver.amazon_s3:
        class: Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
        arguments:
            - "@acme.amazon_s3"
            - "%amazon_s3.bucket%"
        calls:
             # This calls $service->setObjectUrlOption('https', true);
             - [ setObjectUrlOption, [ 'https', true ] ]
        tags:
            - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }
```

You can also use the constructor of the resolver to directly inject multiple options.

``` yaml
services:
    acme.imagine.cache.resolver.amazon_s3:
        class: Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
        arguments:
            - "@acme.amazon_s3"
            - "%amazon_s3.bucket%"
            - "public-read" # AmazonS3::ACL_PUBLIC (default)
            - { https: true, torrent: true }
        tags:
            - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }
```

- [Back to cache resolvers](../cache-resolvers.md)
- [Back to the index](../index.md)
