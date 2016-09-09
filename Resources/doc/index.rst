

LiipImagineBundle
=================

Overview
--------

The `LiipImagineBundle`_ package provides an *image manipulation abstraction toolkit*
for Symfony-based projects. Features include:

* :doc:`Filter Sets <basic-usage>`: Using any Symfony-supported configuration language
  (such as YML and XML), you can create *filter set* definitions that specify
  transformation routines. These include a set of *filters* and *post-processors*, as
  well as other, optional parameters.
* :doc:`Filters <filters>`: A number of built-in filters are provided, allowing for an array of
  common image transformations. Examples include :ref:`thumbnail <filter-thumbnail>`,
  :ref:`scale <filter-scale>`, :ref:`crop <filter-crop>`, :ref:`strip <filter-strip>`,
  and :ref:`watermark <filter-watermark>`, and many more. Additionally,
  :ref:`custom filters <filter-custom>` are supported.
* :doc:`Post-Processors <post-processors>`: A number of build-in post-processors are provided,
  allowing for the modification of the resulting binary file created by filters. Examples include
  :ref:`JpegOptim <post-processor-jpegoptim>`, :ref:`OptiPNG <post-processor-optipng>`,
  :ref:`MozJpeg <post-processor-mozjpeg>`, and :ref:`PngQuant <post-processor-pngquant>`. Additionally,
  :ref:`custom post-processors <post-processors-custom>` are supported.


Chapters
--------

Jump into any of the available chapters to learn more about anything from the basic
usage to the architecture of bundle.

.. toctree::
    :maxdepth: 2

    installation
    introduction
    basic-usage
    filters
    post-processors
    configuration
    data-loaders
    cache-resolvers
    cache-manager
    commands


.. _`LiipImagineBundle`: https://github.com/liip/LiipImagineBundle
