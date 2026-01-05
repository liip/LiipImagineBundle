
.. _data-loaders-asset-mapper:

Asset Mapper Loader (for dev)
============================

The ``AssetMapper`` data loader allows for loading images using Symfony's AssetMapper component.

Configuration
-------------

To enable the Asset Mapper loader, you need to configure it in your loaders section. 
A common use case is to use it in combination with the default filesystem loader using a chain loader, 
especially in development environment.

.. code-block:: yaml

    # config/packages/liip_imagine.yaml
    when@dev:
        liip_imagine:
            loaders:
                asset_mapper:
                    asset_mapper: ~
                chain:
                    chain:
                        loaders: [ asset_mapper, default ]
            data_loader: chain

This configuration creates an ``asset_mapper`` loader and a ``chain`` loader that first tries to find the asset via AssetMapper, and then falls back to the ``default`` loader. Finally, it sets the global ``data_loader`` to use this new ``chain`` loader.
