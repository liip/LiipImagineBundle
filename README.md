LiipImagineBundle
=================

This bundle is a fork of AvalancheImagineBundle which provides easy image
manipulation support for Symfony2. The goal of the fork is to make the
code more extensible and as a result applicable for more use cases.

For more details on the reason for the fork see:
https://github.com/avalanche123/AvalancheImagineBundle/pull/25

For example with this bundle the following is possible:

``` jinja
<img src="{{ '/relative/path/to/image.jpg' | apply_filter('thumbnail') }}" />
````

This will perform the transformation called `thumbnail`, which you can define
to do a number of different things, such as resizing, cropping, drawing,
masking, etc.

This bundle integrates the standalone PHP "[Imagine library](/avalanche123/Imagine)".

## Installation

To install this bundle, you'll need both the [Imagine library](/avalanche123/Imagine)
and this bundle. Installation depends on how your project is setup:

### Step 1: Installation

Add the following lines to your ``deps`` file

```
[Imagine]
    git=http://github.com/avalanche123/Imagine.git
    target=imagine
    version=v0.2.0

[LiipImagineBundle]
    git=http://github.com/liip/LiipImagineBundle.git
    target=bundles/Liip/ImagineBundle
```

Next, update your vendors by running:

``` bash
$ ./bin/vendors install
```

### Step 2: Configure the autoloader

Add the following entries to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...

    'Imagine'   => __DIR__.'/../vendor/imagine/lib',
    'Liip'      => __DIR__.'/../vendor/bundles',
));
```

### Step 3: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...

        new Liip\ImagineBundle\LiipImagineBundle(),
    );
}
```

### Step 4: Register the bundle's routes

Finally, add the following to your routing file:

``` yaml
# app/config/routing.yml

_imagine:
    resource: .
    type:     imagine
```

Congratulations! You're ready to rock your images!

## Basic Usage

This bundle works by configuring a set of filters and then applying those
filters to images inside a template So, start by creating some sort of filter
that you need to apply somewhere in your application. For example, suppose
you want to thumbnail an image to a size of 120x90 pixels:

``` yaml
# app/config/config.yml

liip_imagine:
    filters:
        my_thumb:
            type:    thumbnail
            options: { size: [120, 90], mode: outbound }
```

You've now defined a filter called `my_thumb` that performs a thumbnail transformation.
We'll learn more about available transformations later, but for now, this
new filter can be used immediately in a template:

``` jinja
<img src="{{ '/relative/path/to/image.jpg' | apply_filter('my_thumb') }}" />
```

Or if you're using PHP templates:

``` php
<img src="<?php $this['imagine']->filter('/relative/path/to/image.jpg', 'my_thumb') ?>" />
```

Behind the scenes, the bundles apples the filter(s) to the image on the first
request and then caches the image to a similar path. On the next request,
the cached image would be served directly from the file system.

In this example, the final rendered path would be something like
`/media/cache/my_thumb/relative/path/to/image.jpg`. This is where Imagine
would save the filtered image file.

## Configuration

The default configuration for the bundle looks like this:

``` yaml
liip_imagine:
    web_root:     %kernel.root_dir%/../web
    cache_prefix: /media/cache
    cache:        true
    loader:       ~
    driver:       gd
    formats:      []
    filters:      []
```

There are several configuration options available:

 - `web_root` - must be the absolute path to you application's web root. This
    is used to determine where to put generated image files, so that apache
    will pick them up before handing the request to Symfony2 next time they
    are requested.

    default: `%kernel.root_dir%/../web`

 - `cache_prefix` - this is also used in the path for image generation, so
    as to not clutter your web root with cached images. For example by default,
    the images would be written to the `web/media/cache/` directory.

    default: `/media/cache`

 - `cache` - if to cache the generated image in the local file system

 - `loader` - service id for a custom loader

    default: null (which means the standard filesystem loader is used)

 - `driver` - one of the three drivers: `gd`, `imagick`, `gmagick`

    default: `gd`

 - `formats` - optional list of image formats to which images may be converted to.

 - `filters` - specify the filters that you want to define and use

Each filter that you specify have the following options:

 - `type` - determine the type of filter to be used, refer to *Filters* section for more information
 - `options` - options that should be passed to the specific filter type
 - `path` - used in place of the filter name to determine the path in combination with the global `cache_prefix`
 - `quality` - override the default quality of 100 for the generated images

## Built-in Filters

Currently, this bundles comes with just one built-in filter: `thumbnail`.

### The `thumbnail` filter

The thumbnail filter, as the name implies, performs a thumbnail transformation
on your image. Configuration looks like this:

``` yaml
filters:
    my_thumb:
        type:    thumbnail
        options: { size: [120, 90], mode: outbound }
```

The `mode` can be either `outbound` or `inset`.

## Load your Custom Filters

The ImagineBundle allows you to load your own custom filter classes. The only
requirement is that each filter loader implement the following interface:

    Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface

To tell the bundle about your new filter loader, register it in the service
container and apply the following tag to it (example here in XML):

``` xml
<tag name="liip_imagine.filter.loader" filter="my_custom_filter" />
```

For more information on the service container, see the Symfony2
[Service Container](http://symfony.com/doc/current/book/service_container.html) documentation.

You can now reference and use your custom filter when defining filters you'd
like to apply in your configuration:

``` yaml
filters:
    my_special_filter:
        type:    my_custom_filter
        options: { }
```

For an example of a filter loader implementation, refer to
`Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader`.
