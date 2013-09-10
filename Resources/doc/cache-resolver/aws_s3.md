# AwsS3Resolver

The AwsS3Resolver requires the [aws-sdk-php](https://github.com/aws/aws-sdk-php).

You can add the SDK by adding those lines to your `deps` file.

``` ini
[aws-sdk]
    git=git://github.com/aws/aws-sdk-php.git
```

Afterwards, you only need to configure some information regarding your AWS account and the bucket.

``` yaml
parameters:
    amazon_s3.key: 'your-aws-key'
    amazon_s3.secret: 'your-aws-secret'
    amazon_s3.bucket: 'your-bucket.example.com'
    amazon_s3.region: 'your-bucket-region'
```

Now you can set up the services required:

``` yaml
services:
    acme.amazon_s3:
        class: Aws\S3\S3Client
        factory_class: Aws\S3\S3Client
        factory_method:  factory
        arguments:
            -
                key: %amazon_s3.key%
                secret: %amazon_s3.secret%
                region: %amazon_s3.region%

    acme.imagine.cache.resolver.amazon_s3:
        class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
        arguments:
            - "@acme.amazon_s3"
            - "%amazon_s3.bucket%"
        tags:
            - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }
```

Now you are ready to use the `AwsS3Resolver` by configuring the bundle.
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
