# AwsS3Resolver

The AwsS3Resolver requires the [aws-sdk-php](https://github.com/aws/aws-sdk-php).

You can add the SDK by runnig composer.

```bash
composer require "aws/aws-sdk-php:~2"
```

Afterwards, you only need to configure some information regarding your AWS account and the bucket.

```yaml
parameters:
    amazon.s3.key:    'your-aws-key'
    amazon.s3.secret: 'your-aws-secret'
    amazon.s3.bucket: 'your-bucket.example.com'
    amazon.s3.region: 'your-bucket-region'
```

## Create resolver using factory

```yaml
liip_imagine:
    resolvers:
       profile_photos:
          aws_s3:
              client_config:
                  key:    %amazon.s3.key%
                  secret: %amazon.s3.secret%
                  region: %amazon.s3.region%
              bucket:     %amazon.s3.cache_bucket%
```

## Create resolver as a service

You have to set up the services required:

```yaml
services:
    acme.amazon_s3:
        class: Aws\S3\S3Client
        factory_class: Aws\S3\S3Client
        factory_method:  factory
        arguments:
            -
                key: %amazon.s3.key%
                secret: %amazon.s3.secret%
                region: %amazon.s3.region%

    acme.imagine.cache.resolver.amazon_s3:
        class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
        arguments:
            - "@acme.amazon_s3"
            - "%amazon.s3.bucket%"
        tags:
            - { name: 'liip_imagine.cache.resolver', resolver: 'profile_photos' }
```

## Usage

Now you are ready to use the `AwsS3Resolver` by configuring the bundle.
The following example will configure the resolver is default.

```yaml
liip_imagine:
    cache: profile_photos
```

If you want to use other buckets for other images, simply alter the parameter names and create additional services!

### Additional options

You can use [Cache](./cache.md) and [Proxy](./proxy.md) resolvers in chain with current. You just need to configure them with defined options.

```yaml
liip_imagine:
    resolvers:
       profile_photos:
          aws_s3:
              ...
              proxies: ['http://one.domain.com', 'http://two.domain.com']
              cache: true
```

If enabled both first one will be [Cache](./cache.md), then [Proxy](./proxy.md) and after all process delegates to AwsS3 resolver.

## Object URL Options

In order to make use of the object URL options, you can simply add a call to the service, to alter those options you need.

``` yaml
services:
    acme.imagine.cache.resolver.amazon_s3:
        class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
        arguments:
            - "@acme.amazon_s3"
            - "%amazon_s3.bucket%"
        calls:
             # This calls $service->setObjectUrlOption('Scheme', 'https');
             - [ setObjectUrlOption, [ 'Scheme', 'https' ] ]
        tags:
            - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }
```

You can also use the constructor of the resolver to directly inject multiple options.

``` yaml
services:
    acme.imagine.cache.resolver.amazon_s3:
        class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
        arguments:
            - "@acme.amazon_s3"
            - "%amazon_s3.bucket%"
            - "public-read" # Aws\S3\Enum\CannedAcl::PUBLIC_READ (default)
            - { Scheme: https }
        tags:
            - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }
```

- [Back to cache resolvers](../cache-resolvers.md)
- [Back to the index](../index.md)
