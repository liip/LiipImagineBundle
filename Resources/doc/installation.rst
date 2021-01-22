

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory, and execute the
following command to download the latest stable version of this bundle
and add it as a dependency to your project:

.. code-block:: bash

    $ composer require liip/imagine-bundle

This command requires that `Composer`_ is installed globally, as explained in
the `installation documentation`_ for Composer.


Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding ``new Liip\ImagineBundle\LiipImagineBundle()``
to the bundles array of the ``registerBundles`` method in your project's
``app/AppKernel.php`` file:

.. code-block:: php

    <?php

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new Liip\ImagineBundle\LiipImagineBundle(),
            );

            // ...
        }

        // ...
    }

If you are using Symfony 5.x, enable the bundle by adding ``new Liip\ImagineBundle\LiipImagineBundle()``
to the bundles array of the ``return`` method in your project's
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

        # app/config/routing.yml
        _liip_imagine:
            resource: "@LiipImagineBundle/Resources/config/routing.yaml"

    .. code-block:: xml

        <import resource="@LiipImagineBundle/Resources/config/routing.yaml"/>

Congratulations; you are ready to rock your images!


.. _`installation documentation`: https://getcomposer.org/doc/00-intro.md
.. _`Composer`: https://getcomposer.org/
