
.. _cache-resolver-amazon-s3:

Amazon S3 Resolver
==================

The ``AmazonS3Resolver`` resolver enables cache resolution using Amazon S3.

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

To begin, you must assign your Amazon key, secret, and bucket to their respective parameters.

.. code-block:: yaml

    # app/config/config.yml or app/config/parameters.yml

    parameters:
        amazon_s3.key:    "your-aws-key"
        amazon_s3.secret: "your-aws-secret"
        amazon_s3.bucket: "your-bucket.example.com"

Prerequisites
-------------

Next, you must define the required services.

.. code-block:: yaml

    # app/config/services.yml

    services:

        acme.imagine.cache.resolver.amazon_s3:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "amazon_s3" }

        acme.amazon_s3:
            class: AmazonS3
            arguments:
                -
                    key:    "%amazon_s3.key%"
                    secret: "%amazon_s3.secret%"

Usage
-----

After configuring ``AmazonS3Resolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: amazon_s3


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
                cache: amazon_s3
                filters:
                    # the filter list

.. tip::

    If you want to use other buckets for other images, simply alter the parameter
    names and create additional services.


Object URL Options
------------------

In order to make use of the object URL options, you can simply add a call to the
service, to alter those options you need.

.. code-block:: yaml

    # app/config/services.yml

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
                - { name: "liip_imagine.cache.resolver", resolver: "amazon_s3" }

You can also use the constructor of the resolver to directly inject multiple
options.

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.imagine.cache.resolver.amazon_s3:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"
                - "public-read" # AmazonS3::ACL_PUBLIC (default)
                - { https: true, torrent: true }
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "amazon_s3" }


.. _`aws-sdk-php`: https://github.com/amazonwebservices/aws-sdk-for-php
.. _`Composer`: https://getcomposer.org/
.. _`installation documentation`: https://getcomposer.org/doc/00-intro.md
