

Asset Versioning
================

You can configure Symfony to append an `asset version`_ to the asset URLs. This
is used to bust the cache after changes if you make clients cache assets for a
long time.

If you use the Twig ``asset`` function to resolve an image file name relative
to the public web folder and then put the result into ``imagine_filter``, the
query string would normally be considered part of the file name, making it so
that the file can not be found.

Since LiipImagineBundle version 2.7, we integrate with the configuration
setting for ``framework.assets.version``. It strips the version from the file
name and appends it to the resulting image URL so that the file is found and
cache busting is used.

Since LiipImagineBundle version 2.12, we integrate with the configuration
setting for ``framework.assets.json_manifest_path``. The manifest file is used
to lookup the location of the actual file, and append the versioning string to
the resulting image URL so that cache busting is used.

Cache Busting
~~~~~~~~~~~~~

If you allow clients to cache your images, it can make sense to use asset
versioning to bust the cache of your images. This can help for example after
you changed the settings of a filter set.

If you use ``framework.assets.version``, change the asset version in that case.
If you use ``framework.assets.json_manifest_path``, then rebuild the manifest
in your asset pipeline. Note that your versioning string might be calculated
using a content hash. Changing a filter setting in these cases will *not* bust
the previous cache. Either rename your filter in that case or use a different
versioning strategy within your asset pipeline that ensures a new hash for each
build.
If you do not use Symfony asset versioning, set the
``liip_imagine.twig.assets_version`` parameter. Note that you still need to
clear/refresh the cached images to have them updated, the asset version is only
relevant for HTTP caches but not for the LiipImagineBundle cache.

This approach works well for changed filter sets, but not for changes to
individual images. For those, the best approach is to have a versioning in the
file name or using the ``CacheResolveEvent`` to change the URL of the image.

Troubleshooting
~~~~~~~~~~~~~~~

If the version query is not handled correctly, check the logs during container
building. If you use a custom asset versioning, you can configure the
``liip_imagine.twig.assets_version`` parameter with the version string to look
for.

To completely disable asset version handling by LiipImagineBundle, you can set
``liip_imagine.twig.assets_version`` to ``false``. But be aware that if you
configured Symfony to append an asset version, you now won't be able to use the
``asset`` Twig function with the ``imagine_filter``.

.. _`asset version`: https://symfony.com/doc/current/reference/configuration/framework.html#reference-framework-assets-version
