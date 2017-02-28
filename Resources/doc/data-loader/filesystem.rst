
.. _data-loaders-filesystem:

File System Loader
==================

The ``FileSystem`` data loader allows for loading images from local file system paths.

.. tip::

    If you don't configure anything, this loader is used by default.


Configuration
-------------

To set this loader for a specific context called ``profile_photos``, use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            profile_photos:
                filesystem: ~

By default, Symfony's ``web/`` directory is registered as a data root to load
assets from. For many installations this will be sufficient, but sometime you
may need to load images from other locations. To do this, you must set the
``data_root`` parameter.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            default:
                filesystem:
                    data_root: /path/to/source/images/dir


As of version ``1.7.2`` you can register multiple data roots and the file locator
will search each for the requested file.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            default:
                filesystem:
                    data_root:
                        - /path/foo
                        - /path/bar

As of version ``1.7.3`` you ask for the public resource paths from all registered bundles
to be auto-registered as data roots. This allows you to load assets from the
``Resources/public`` folders that reside within the loaded bundles. To enable this
feature, set the ``bundle_resources.enabled`` configuration option to ``true``.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            default:
                filesystem:
                    bundle_resources:
                        enabled: true

If you want to register some of the ``Resource/public`` folders, but not all, you can do
so by blacklisting the bundles you don't want registered or whitelisting the bundles you
do want registered. For example, to blacklist (not register) the bundles "FooBundle" and
"BarBundle", you would use the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            default:
                filesystem:
                    bundle_resources:
                        enabled: true
                        access_control_type: blacklist
                        access_control_list:
                            - FooBundle
                            - BarBundle

Alternatively, if you want to whitelist (only register) the bundles "FooBundle" and "BarBundle",
you would use the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            default:
                filesystem:
                    bundle_resources:
                        enabled: true
                        access_control_type: whitelist
                        access_control_list:
                            - FooBundle
                            - BarBundle

Lastly, as of version `1.7.3`, you can name your data roots and reference them when calling resources.
This can be useful for a number of reasons, such as wanting to be explicit, but it most useful when
you have multiple data roots paths that both contain a file of the same name. In this situation, you
can name your data root paths by providing an index in the `data_root` configuration array (note that
auto-registered bundle resource paths have indices defined of the bundle's short class name).

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            default:
                filesystem:
                    data_root:
                        foo: /a/foo/path
                        bar: /a/bar/path

Given the above configuration, you can explicitly request a root path using the format ``@index:path/to/file.ext``.
For example, to request the file ``/a/foo/path/with/file.ext`` you can pass ``@foo:with/file.ext`` as the filename.