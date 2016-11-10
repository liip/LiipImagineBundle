

Data Loaders
============

A number of built-in data loaders are available:

.. toctree::
    :maxdepth: 1
    :glob:

    data-loader/*


Other Data Loaders
------------------

* `Doctrine PHPCR-ODM`_: You can include the ``CmfMediaBundle`` alone if you just
  want to use the images but no other Symfony CMF features.


.. _data-loaders-custom:

Custom Data Loader
------------------

You can easily define your own, custom data loaders to allow you to retrieve you
image data from any imaginable backend. Creating a custom data loader begins by creating
a class that implements the ``LoaderInterface``, as shown below.

.. code-block:: php

    interface LoaderInterface
    {
        public function find($path);
    }

As defined in ``LoaderInterface``, the only required method is one named ``find``,
which is provided a relative image path as its singular parameter, and
subsequently provides an instance of ``BinaryInterface`` in return.

The following is a template for creating your own data loader. You must provide
the implementation for the ``find`` method to create a valid data loader.

.. code-block:: php

    namespace AppBundle\Imagine\Binary\Loader;

    use Liip\ImagineBundle\Binary\BinaryInterface;
    use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
    use Liip\ImagineBundle\Model\Binary;

    class MyCustomDataLoader implements LoaderInterface
    {
        /**
         * @param mixed $path
         *
         * @return BinaryInterface
         */
        public function find($path)
        {
            $data = /** @todo: implement logic to read image data */
            $mime = /** @todo: implement logic to determine image mime-type */

            // return binary instance with data
            return new Binary($data, $mime);
        }
    }

Once you have defined your custom data loader, you must define it as a service and tag it
with ``liip_imagine.binary.loader``.

.. note::

    For more information on the Service Container, reference the official
    `Symfony Service Container documentation`_.

To register ``AppBundle\Imagine\Binary\Loader\MyCustomDataLoader`` with the name
``my_custom_data_loader``, you would use the following configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        services:
            imagine.data.loader.my_custom:
                class: AppBundle\Imagine\Binary\Loader\MyCustomDataLoader
                arguments:
                    - "@liip_imagine"
                    - "%liip_imagine.formats%"
                tags:
                    - { name: "liip_imagine.binary.loader", loader: my_custom_data_loader }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="imagine.data.loader.my_custom" class="AppBundle\Imagine\Binary\Loader\MyCustomDataLoader">
            <tag name="liip_imagine.data.loader" loader="my_custom_data_loader" />
            <argument type="service" id="liip_imagine" />
            <argument type="parameter" id="liip_imagine.formats" />
        </service>

You can set your custom data loader by adding it to the configuration as the new default
loader:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        data_loader: my_custom_data_loader

Alternatively, you can only set the custom data loader for just a specific filter set:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_special_style:
                data_loader: my_custom_data_loader
                filters:
                    # your filters


.. _`Symfony Service Container documentation`: http://symfony.com/doc/current/book/service_container.html
.. _`Doctrine PHPCR-ODM`: http://symfony.com/doc/master/cmf/bundles/media.html#liipimagine
