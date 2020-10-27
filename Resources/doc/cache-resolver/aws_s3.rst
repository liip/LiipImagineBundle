
.. _cache-resolver-aws-s3:

AWS S3 Resolver
===============

The ``AwsS3Resolver`` resolver enables cache resolution using Amazon S3.


Dependencies
------------

This cache resolver requires the `aws-sdk-php`_ library, which can be installed
by executing the following command in your project directory:

.. code-block:: bash

    $ composer require aws/aws-sdk-php


.. note::

    This command requires that `Composer`_ is installed globally, as explained in
    their `installation documentation`_.


Configuration
-------------

To begin, you must assign your AWS key, secret, bucket, and region to their respective parameters.

.. code-block:: yaml

    # app/config/config.yml or app/config/parameters.yml

    parameters:
        amazon.s3.key:     "your-aws-key"
        amazon.s3.secret:  "your-aws-secret"
        amazon.s3.bucket:  "your-bucket.example.com"
        amazon.s3.region:  "your-bucket-region"
        amazon.s3.version: "2006-03-01"


Prerequisites
-------------

Create Resolver from a Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        resolvers:
            aws_s3_resolver:
                aws_s3:
                    client_config:
                        credentials:
                            key:    "%amazon.s3.key%"
                            secret: "%amazon.s3.secret%"
                        region: "%amazon.s3.region%"
                        version: "%amazon.s3.version%"
                    bucket: "%amazon.s3.bucket%"
                    get_options:
                        Scheme: https
                    put_options:
                        CacheControl: "max-age=86400"


.. tip::

    If using `aws-sdk-php`_ < ``3.0.0``, you must omit the ``credentials`` key and instead
    place the ``key`` and ``secret`` keys at the same level as ``region`` and ``bucket``.

    .. code-block:: yaml

        # app/config/services.yml

        services:
            aws_s3:
                client_config:
                    key:    "%amazon.s3.key%"
                    secret: "%amazon.s3.secret%"
                    region: "%amazon.s3.region%"
                    bucket: "%amazon.s3.bucket%"

Create Resolver as a Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

While creating the resolver through a factory using the configuration above does the job for most use cases,
it can sometimes be neccessary to create the resolver as a service.

.. tip::
    For example, this is required if you do not want to hard code AWS credentials for your resolver, but
    instead make it use the `Default Credential Provider Chain`_ within AWS (for example, because you are
    running this on an EC2 instance which is already attached to an IAM role with the required permissions).
    In this case, simply setting up the resolver through a factory configuration block and leaving out
    the credentials block won't cut it. Instead, you need to set up the resolver as a service as shown
    below, passing an S3Client.

You have to set up the services required:

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.amazon_s3:
            class: Aws\S3\S3Client
            factory: [Aws\S3\S3Client, factory]
            arguments:
                -
                    credentials: { key: "%amazon.s3.key%", secret: "%amazon.s3.secret%" }
                    region: "%amazon.s3.region%"
                    version: "%amazon.s3.version%"

        acme.imagine.cache.resolver.aws_s3_resolver:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon.s3.bucket%"
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "aws_s3_resolver" }


.. tip::

    If using `aws-sdk-php`_ < ``3.0.0``, you must omit the ``credentials`` key and instead
    place the ``key`` and ``secret`` keys at the same level as ``region`` and ``bucket``.

    .. code-block:: yaml

        # app/config/services.yml

        services:
            acme.amazon_s3:
                # ...
                arguments:
                    -
                        key: "%amazon.s3.key%"
                        secret: "%amazon.s3.secret%"
                        region: "%amazon.s3.region%"

        acme.amazon_s3:
            class: Aws\S3\S3Client
            factory: [Aws\S3\S3Client, factory]
            arguments:
                -
                    credentials: { key: "%amazon.s3.key%", secret: "%amazon.s3.secret%" }
                    region: "%amazon.s3.region%"
                    version: "%amazon.s3.version%"


Usage
-----

After configuring ``AwsS3Resolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: aws_s3_resolver


Usage on a Specific Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, you can set ``AmazonS3Resolver`` as the cache resolver for a specific
filter set using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: aws_s3_resolver
                filters:
                    # the filter list

.. tip::

    If you want to use other buckets for other images, simply alter the parameter
    names and create additional services.


Additional Options
------------------

You can use :ref:`Cache <cache-resolver-cache>` and :ref:`Proxy <cache-resolver-proxy>` resolvers in chain with
current. You just need to configure them with defined options.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        resolvers:
           aws_s3_resolver:
              aws_s3:
                  #...
                  proxies: ["http://one.domain.com", "http://two.domain.com"]
                  cache: true


If enabled both first one will be :ref:`Cache <cache-resolver-cache>`, then
:ref:`Proxy <cache-resolver-proxy>` and after all process delegates to AwsS3 resolver.


Object GET Options
~~~~~~~~~~~~~~~~~~

In order to make use of the object GET options, you can simply add a call to the
service, to alter those options you need.

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.imagine.cache.resolver.aws_s3_resolver:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
            calls:
                 # This calls $service->setGetOption('Scheme', 'https');
                 - [ setGetOption, [ Scheme, https ] ]
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "aws_s3_resolver" }


You can also use the constructor of the resolver to directly inject multiple options.

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.imagine.cache.resolver.aws_s3_resolver:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
                - "public-read" # Aws\S3\Enum\CannedAcl::PUBLIC_READ (default)
                - { Scheme: https }
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "aws_s3_resolver" }


Object PUT Options
~~~~~~~~~~~~~~~~~~

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

    # app/config/services.yml

    services:
        acme.imagine.cache.resolver.aws_s3_resolver:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
            calls:
                 # This calls $service->setPutOption('CacheControl', 'max-age=86400');
                 - [ setPutOption, [ CacheControl, "max-age=86400" ] ]
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "aws_s3_resolver" }


You can also use the constructor of the resolver to directly inject multiple options.

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.imagine.cache.resolver.aws_s3_resolver:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
                - "public-read" # Aws\S3\Enum\CannedAcl::PUBLIC_READ (default)
                - { Scheme: https }
                - { CacheControl: "max-age=86400" }
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "aws_s3_resolver" }


.. _`aws-sdk-php`: https://github.com/amazonwebservices/aws-sdk-for-php
.. _`Composer`: https://getcomposer.org/
.. _`installation documentation`: https://getcomposer.org/doc/00-intro.md
.. _`S3 SDK documentation`: http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_putObject
.. _`Default Credential Provider Chain`: https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html
