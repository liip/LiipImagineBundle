

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory, and execute the
following command to download the latest stable version of this bundle
and add it as a dependency to your project:

```bash
composer require liip/imagine-bundle
```

If you accept the Symfony Flex recipe during installation, the bundle is
registered, routing set up and the configuration skeleton file is created. You
can now adapt the configuration to your needs.
Otherwise, you need to configure the bundle with the next steps.

Step 2: Enable the Bundle
-------------------------
Enable the bundle by adding ``new Liip\ImagineBundle\LiipImagineBundle() to the bundles array of the ``return`` method in your project's
``config/bundles.php`` file:

.. code-block:: php

    <?php

    return [
        // ...

        Liip\ImagineBundle\LiipImagineBundle::class => ['all' => true]
    ];



Step 3: Register the bundle's routes
------------------------------------

Finally, register this bundle's routes by add the following to your project's
routing file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/route/liip_imagine.yml
        _liip_imagine:
            resource: "@LiipImagineBundle/Resources/config/routing.yaml"

    .. code-block:: xml

        <import resource="@LiipImagineBundle/Resources/config/routing.yaml"/>

Congratulations; you are ready to rock your images!
