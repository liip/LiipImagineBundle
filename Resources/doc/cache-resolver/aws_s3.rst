AwsS3Resolver
=============

The AwsS3Resolver requires the `aws-sdk-php`_ library. Open a command
console, enter your project directory and execute the following command to
download the latest stable version of this library:

.. code-block:: bash

    $ composer require aws/aws-sdk-php

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.

Afterwards, you only need to configure some information regarding your AWS
account and the bucket.

.. code-block:: yaml

    parameters:
        amazon.s3.key:    'your-aws-key'
        amazon.s3.secret: 'your-aws-secret'
        amazon.s3.bucket: 'your-bucket.example.com'
        amazon.s3.region: 'your-bucket-region'

Create resolver using factory
-----------------------------

.. code-block:: yaml

    liip_imagine:
        resolvers:
           profile_photos:
              aws_s3:
                  client_config:
                      key:    %amazon.s3.key%
                      secret: %amazon.s3.secret%
                      region: %amazon.s3.region%
                  bucket:     %amazon.s3.cache_bucket%
                  get_options:
                      Scheme: 'https'
                  put_options:
                      CacheControl: 'max-age=86400'

Create resolver as a service
----------------------------

You have to set up the services required:

.. code-block:: yaml

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

Usage
-----

Now you are ready to use the ``AwsS3Resolver`` by configuring the bundle.
The following example will configure the resolver is default.

.. code-block:: yaml

    liip_imagine:
        cache: profile_photos

If you want to use other buckets for other images, simply alter the parameter
names and create additional services!

Additional options
------------------

You can use :doc:`Cache <cache>` and :doc:`Proxy <proxy>` resolvers in chain with
current. You just need to configure them with defined options.

.. code-block:: yaml

    liip_imagine:
        resolvers:
           profile_photos:
              aws_s3:
                  ...
                  proxies: ['http://one.domain.com', 'http://two.domain.com']
                  cache: true

If enabled both first one will be :doc:`Cache <cache>`, then :doc:`Proxy <proxy>`
and after all process delegates to AwsS3 resolver.

Object GET Options
------------------

In order to make use of the object GET options, you can simply add a call to the
service, to alter those options you need.

.. code-block:: yaml

    services:
        acme.imagine.cache.resolver.amazon_s3:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
            calls:
                 # This calls $service->setGetOption('Scheme', 'https');
                 - [ setGetOption, [ 'Scheme', 'https' ] ]
            tags:
                - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }

You can also use the constructor of the resolver to directly inject multiple options.

.. code-block:: yaml

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

Object PUT Options
------------------

Similar to Object GET Options you can configure additional options to be passed
to S3 when storing objects. This is useful, for example, to configure Cache-
control headers returned when serving object from S3. See `S3 SDK documentation`_
for the list of available options.

Note, that the following options are configured automatically and will be
ignored, even if you configure it via ObjectOptions:

* ``ACL``
* ``Bucket``
* ``Key``
* ``Body``
* ``ContentType``

In order to make use of the object PUT options, you can simply add a call to the
service, to alter those options you need.

.. code-block:: yaml

    services:
        acme.imagine.cache.resolver.amazon_s3:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
            calls:
                 # This calls $service->setPutOption('CacheControl', 'max-age=86400');
                 - [ setPutOption, [ 'CacheControl', 'max-age=86400' ] ]
            tags:
                - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }

You can also use the constructor of the resolver to directly inject multiple options.

.. code-block:: yaml

    services:
        acme.imagine.cache.resolver.amazon_s3:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
                - "public-read" # Aws\S3\Enum\CannedAcl::PUBLIC_READ (default)
                - { Scheme: https }
                - { CacheControl: 'max-age=86400' }
            tags:
                - { name: 'liip_imagine.cache.resolver', resolver: 'amazon_s3' }

.. _`aws-sdk-php`: https://github.com/amazonwebservices/aws-sdk-for-php
.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md
.. _`S3 SDK documentation`: http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_putObject
