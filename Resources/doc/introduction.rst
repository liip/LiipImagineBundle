

Introduction
============

Basic Data Flow
---------------

The core feature of this bundle is to provide a way to alter images in certain
ways and cache the altered versions. There are several components involved to
get this done.


Retrieving the original image
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The first step is to retrieve the original image, the one you address.

In order to retrieve such an image, there are so-called ``DataLoader`` those
implement the ``Liip\ImagineBundle\Binary\Loader\LoaderInterface``. Those
loaders are typically managed by the ``DataManager`` and automatically wired
with it, using dependency injection.

How a specific ``DataLoader`` retrieves the image, is up to the loader. The most
simple way is to read a file from the local filesystem. This is implemented by
the ``Liip\ImagineBundle\Binary\Loader\FileSystemLoader``, which is set by
default. You could also create a random image on the fly using drawing
utilities, or read a binary stream from any stream registered.

The most important parts about those ``DataLoader``:

1. They ``find`` a single image based on a given identifier.
2. They return a ready-to-use ``Imagine\Image\ImageInterface``.

Check out the :doc:`chapter about data loaders <data-loaders>` to learn more about them.


Apply filters on the original image
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now, that we fetched an image, we can alter the image in any way. You can create
a resized version, a thumbnail, add a watermark, convert it to gray-scale,
resample the image, change its resolution ... you get the idea. Any alteration is
called a ``Filter``, derived from the naming within the Imagine library.

The responsibility of applying such a filter as bound to a ``FilterLoader``,
which are typically managed by the ``FilterManager``. Those ``FilterLoader``
implement the ``Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface``. The
``FilterManager`` is aware of so-called ``filter_sets``. A filter set may define
multiple filters to be applied on the result of each predecessor.

The filter has one objective: Apply itself on the provided image (loaded by the
``DataLoader``). It receives options to configure the actual result of it, to
customize the outcome.

Check out the :doc:`chapter about filters <filters>` to learn more about them.


Cache the filtered image
~~~~~~~~~~~~~~~~~~~~~~~~

The filtered - to be cached - image is the image which results after applying
all filters within a filter set.

In order to not apply each filter again on the same image, which will by most
means result in the same filtered image, this result will be cached. This
caching is managed by the ``CacheManager`` which manages all so-called
``CacheResolver``.

The default ``CacheResolver`` is the ``WebPathResolver``, which will cache the
image in the web directory as a static file, so the web server won't call the
application stack anymore on those images. The images will be created upon first
request and will remain in their static cached version until removed.

A ``CacheResolver`` implements the ``Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface``.

It handles the so-called ``path``, which is the identifier you use, when
addressing the original image, e.g. in your template. This path relates to the
path used in the ``DataLoader``.

The responsibilities of the ``CacheResolver`` are:

1. to resolve a given ``path`` into a ``Response``, if possible,
2. store given content under a given ``path`` to be resolved later,
3. generate an URI to address the cached image directly,
4. remove a cached image.

Check out the :doc:`chapter about cache resolvers <cache-resolvers>` to learn more about them.
