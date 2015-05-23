Built-In DataLoader
===================

* :doc:`FileSystem <data-loader/filesystem>`
* :doc:`MongoDB GridFS <data-loader/gridfs>`
* :doc:`Stream <data-loader/stream>`

Other data loaders
------------------

* `Doctrine PHPCR-ODM`_: you can include the CmfMediaBundle alone if you just
  want to use the images but no other CMF features.

Custom image loaders
--------------------

The ImagineBundle allows you to add your custom image loader classes. The only
requirement is that each data loader implements the following interface:
``Liip\ImagineBundle\Binary\Loader\LoaderInterface``.

To tell the bundle about your new data loader, register it in the service
container and apply the ``liip_imagine.binary.loader`` tag to it (example here
in XML):

.. code-block:: xml

    <service id="acme_imagine.data.loader.my_custom" class="Acme\ImagineBundle\Binary\Loader\MyCustomDataLoader">
        <tag name="liip_imagine.binary.loader" loader="my_custom_data" />
        <argument type="service" id="liip_imagine" />
    </service>

For more information on the service container, see the `Symfony Service Container`_
documentation.

You can set your custom data loader by adding it to the configuration as the new default
loader as follows:

.. code-block:: yaml

    liip_imagine:
        data_loader: my_custom_data

Alternatively you can only set the custom data loader for just a specific filter set:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_special_style:
                data_loader: my_custom_data
                filters:
                    my_custom_filter: { }

For an example of a data loader implementation, refer to
``Liip\ImagineBundle\Binary\Loader\FileSystemLoader``.

.. _`Doctrine PHPCR-ODM`: http://symfony.com/doc/master/cmf/bundles/media.html#liipimagine
.. _`Symfony Service Container`: http://symfony.com/doc/current/book/service_container.html
