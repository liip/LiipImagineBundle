

Symfony UX Image
================

`Symfony UX Image`_ renders responsive images: it builds the ``srcset``, the
``sizes`` attribute and the layout ``style`` of an ``<img>`` or a ``<picture>``,
and delegates every transformation to a URL-based *provider*. The included providers
are all hosted CDNs. This bundle adds one that runs fully on your own infrastructure.

.. caution::

    Symfony UX Image is experimental and may change. The
    provider is only registered when ``symfony/ux-image`` is installed.

Setup
-----

Install the component next to this bundle:

.. code-block:: terminal

    $ composer require symfony/ux-image

Then point its DSN at this bundle:

.. code-block:: bash

    # .env
    UX_IMAGE_DSN=liipimagine://ux_image

That is the whole setup. The bundle declares the ``ux_image`` filter sets the
DSN refers to, so nothing has to be added under ``liip_imagine.filter_sets``.

Now render an image:

.. code-block:: html+twig

    <twig:ux:picture src="/images/hero.jpg" alt="Hero" width="800" height="450" />

``src`` is the path as the :doc:`data loader <data-loaders>` sees it, with any
leading slash stripped. With the default ``filesystem`` loader rooted at your
public directory, that is the public path of the image.

Every URL in the resulting ``srcset`` is a signed :doc:`runtime filter URL
<cache-manager>`, transformed and cached on first request like any other image
of this bundle.

Use ``<twig:ux:picture>`` rather than ``<twig:ux:image>``: it emits separate
``<source type="image/...">`` per format, which is how this provider offers
WebP with a JPEG fallback. ``<twig:ux:image>`` renders a single ``<img>`` tag and
therefore only the last format of the negotiated list, unless ``auto_format``
is on.

DSN options
-----------

The host of the DSN names the filter set to render through. To use the default
``ux_image``, you can specify just ``liipimagine:`` without even the ``//``.

=================== ========================== ========================================================
Option              Default                    Description
=================== ========================== ========================================================
``resolver``        the filter set's resolver  The :doc:`cache resolver <cache-resolvers>` to store to
``reference_type``  ``absolute_url``           Also ``absolute_path``, ``network_path``, ``relative_path``
``formats``         ``webp,jpeg,png``          The formats the provider offers, see `Formats`_ below
``auto_format``     ``webp.generate``          Whether the format is negotiated per request, see `Formats`_
=================== ========================== ========================================================

For example, to deliver through a dedicated resolver and emit relative URLs:

.. code-block:: bash

    UX_IMAGE_DSN=liipimagine://ux_image?resolver=s3&reference_type=absolute_path

Formats
-------

We use one filter set per format. The names are derieved from the base filter
set: ``ux_image_webp``, ``ux_image_jpeg`` and ``ux_image_png``. The bundle
declares those automatically, if you do not specify a custom configuration.

To offer another format, declare its filter set yourself and list it in the
DSN. AVIF, for example, needs an Imagine driver built with AVIF support:

.. code-block:: yaml

    # config/packages/liip_imagine.yaml
    liip_imagine:
        filter_sets:
            ux_image_avif:
                format: avif

.. code-block:: bash

    UX_IMAGE_DSN=liipimagine://ux_image?formats=avif,webp,jpeg

If you specify a non-default format in the DSN without declaring a filter for it,
rendering the template will fail with an error about the missing filter.

``auto_format`` tells Symfony UX Image that the provider picks the format from
the request itself. That is true when :doc:`WebP generation <configuration>` is
enabled, where the controller serves a WebP variant based on the request
``Accept`` header, so the option defaults to ``liip_imagine.webp.generate``.

.. note::

    The ``quality`` prop of Symfony UX Image is ignored. Quality is a property
    of a filter set, out of reach of a runtime configuration, so it is set on
    the ``ux_image`` filter sets instead - through
    ``liip_imagine.default_filter_set_settings.quality``, or by declaring the
    filter sets yourself.

How a transformation is expressed
---------------------------------

=============================== ===================================================================
Transformation                  Filter
=============================== ===================================================================
``width`` and ``height``, cover :ref:`fixed <filter-fixed>`, which fills the box exactly and crops
``width`` and ``height``,       :ref:`thumbnail <filter-thumbnail>` in ``inset`` mode
``contain``
only one of the two             :ref:`thumbnail <filter-thumbnail>`, deriving the other dimension
neither                         none, the image is only re-encoded by the filter set
=============================== ===================================================================

A cover fit enlarges a source that is smaller than the requested box, as
Symfony UX Image specifies. A contain fit and a single dimension do not: those
go through ``thumbnail``, which never upscales, so a breakpoint wider than the
source yields the source width.

Using your own filter sets
--------------------------

The declared filter sets carry no filters of their own. To specify your own base
configuration (e.g. for a watermark or some post-processor or ``default_image```),
declare the ``ux_image`` filter set:

.. code-block:: yaml

    # config/packages/liip_imagine.yaml
    liip_imagine:
        filter_sets:
            ux_image:
                quality: 85
                default_image: /images/missing.png
                filters:
                    watermark:
                        image: '%kernel.project_dir%/public/images/watermark.png'

The per-format filter sets inherit from this base set. If you need to tweak a specific
image format, you can declare that filter set explicitly to replace it - then it inherits
nothing, but forces the format to the format indicated in the name.

A single image can render through an entirely different filter set with the
``filter`` operation, and store through another resolver with ``resolver``:

.. code-block:: html+twig

    <twig:ux:picture src="/images/hero.jpg" alt="Hero" width="800"
        :operations="{liipimagine: {filter: 'hero', resolver: 's3'}}" />

Operations are keyed by provider name so that they do not silently vanish when
the application switches provider. A filter set that is not declared fails when
the template is rendered rather than when the image is requested.

Things to know
--------------

* A ``<picture>`` with two formats over four breakpoints is eight distinct cache
  entries per image, and eight resolver lookups per render. That is cheap with
  the :doc:`web path resolver <cache-resolver/web_path>`, but one request each
  with a remote one - put a :doc:`cache resolver <cache-resolvers>` in front of
  those.
* ``liip:imagine:cache:resolve`` cannot warm these URLs, because the
  transformation only exists in the signed URL. The command therefore skips the
  declared ``ux_image`` filter sets.
* The URLs are signed with ``kernel.secret``. Changing it invalidates every
  URL already rendered.

.. _`Symfony UX Image`: https://symfony.com/bundles/ux-image/current/index.html
