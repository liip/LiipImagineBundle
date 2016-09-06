# LiipImagineBundle

|       Travis-CI        |        Style-CI         |        Downloads        |         Release         |
|:----------------------:|:-----------------------:|:-----------------------:|:-----------------------:|
| [![Travis](https://src.run/shield/liip/LiipImagineBundle/travis.svg)](http://travis-ci.org/liip/LiipImagineBundle) | [![Style CI](https://src.run/shield/liip/LiipImagineBundle:2515323/styleci.svg)](https://styleci.io/repos/2515323) |[![Downloads](https://src.run/shield/liip/LiipImagineBundle/packagist_dt.svg)](https://packagist.org/packages/liip/imagine-bundle) | [![Latest Stable Version](https://src.run/shield/liip/LiipImagineBundle/packagist_v.svg)](https://packagist.org/packages/liip/imagine-bundle) | 

*This bundle provides an image manipulation abstraction toolkit for
[Symfony](http://symfony.com/)-based projects.*

## Overview

- [Filter Sets](http://symfony.com/doc/master/bundles/LiipImagineBundle/basic-usage.html):
  Using any Symfony-supported configuration language (such as YML and XML), 
  you can create *filter set* definitions that specify transformation routines. 
  These include a set of *filters* and *post-processors*, as well as other,
  optional parameters.
- [Filters](http://symfony.com/doc/master/bundles/LiipImagineBundle/filters.html):
  Many built-in filters are provided, allowing the application of common 
  transformations. Examples include `thumbnail`, `scale`, `crop`, `strip`, `watermark`,  
  and many more. Additionally, [custom filters](http://symfony.com/doc/master/bundles/LiipImagineBundle/filters.html#filter-custom) 
  are supported.
- [Post-Processors](http://symfony.com/doc/master/bundles/LiipImagineBundle/post-processors.html):
  This component allows for the modification of the resulting binary file 
  created by filters. Examples include `jpegoptim`, `optipng`, `cjpeg`, 
  and `pngquant`. Additionally, [custom post-processors](http://symfony.com/doc/master/bundles/LiipImagineBundle/post-processors.html#post-processors-custom) 
  are supported.


### Example

Suppose you defined a `my_thumb` filter set, which can be configured to 
perform any number of different transformations. The simplest invocation would 
be to pipe the path of your image to the provided `imagine_filter` Twig 
filter.

```twig
<img src="{{ '/relative/path/to/image.jpg' | imagine_filter('my_thumb') }}" />
```


### Attribution

- Thanks to the many [contributors](https://github.com/liip/LiipImagineBundle/graphs/contributors) 
  who have dedicated their time and code to this project.

- The standalone PHP
  [Imagine Library](https://github.com/avalanche123/Imagine)
  is used by this bundle for image transformations.

- This package was forked from
  [AvalancheImagineBundle](https://github.com/avalanche123/AvalancheImagineBundle)
  with the goal of making the code more extensible. Reference
  [AvalancheImagineBundle#25](https://github.com/avalanche123/AvalancheImagineBundle/pull/25)
  for additional information on the reasoning for this fork.


## Setup


### Installation

Using this package is similar to all Symfony bundles. The following steps must 
be performed

1. [Download the Bundle](http://symfony.com/doc/master/bundles/LiipImagineBundle/installation.html#step-1-download-the-bundle)
2. [Enable the Bundle](http://symfony.com/doc/master/bundles/LiipImagineBundle/installation.html#step-2-enable-the-bundle)
3. [Register the Routes](http://symfony.com/doc/master/bundles/LiipImagineBundle/installation.html#step-3-register-the-bundle-s-routes)

Detailed setup instructions can be found in the 
[installation](http://symfony.com/doc/master/bundles/LiipImagineBundle/installation.html)
chapter of the documentation.


### Configuration

Detailed information on all available configuration options can be found in the
[configuration](http://symfony.com/doc/master/bundles/LiipImagineBundle/configuration.html)
chapter of the documentation.


## Usage Primer

Generally, this bundle works by applying *filter sets* to images from inside
a template. Your *filter sets* are defined within the application's configuration
file (often `app/config/config.yml`) and are comprised of a collection of
*filters*, *post-processors*, and other optional parameters.

We'll learn more about *post-processors* and other available parameters later,
but for now lets focus on how to define a simple *filter set* comprised of a
few *filters*.


### Create Thumbnails

Before we get started, there is a small amount of configuration needed to ensure
our [data loaders](http://symfony.com/doc/master/bundles/LiipImagineBundle/data-loaders.html)
and [cache resolvers](http://symfony.com/doc/master/bundles/LiipImagineBundle/cache-resolvers.html)
operate correctly. Use the following boilerplate in your configuration file.

```yml
# app/config/config.yml

liip_imagine :

    # configure resolvers
    resolvers :

        # setup the default resolver
        default :

            # use the default web path
            web_path : ~

    # your filter sets are defined here
    filter_sets :

        # use the default cache configuration
        cache : ~
```

With the basic configuration in place, we'll start with an example that fulfills a
common use-case: creating thumbnails. Lets assume we want the resulting thumbnails
to have the following transformations applied to them:

- Scale and crop the image to 120x90px.
- Add a 2px black border to the scaled image.
- Adjust the image quality to 75.

Adding onto our boilerplate from above, we need to define a *filter set* (which we'll
name `my_thumb`) with two *filters* configured: the `thumbnail` and `background`
*filters*.

```yml
# app/config/config.yml

liip_imagine :
    resolvers :
        default :
            web_path : ~

    filter_sets :
        cache : ~

        # the name of the "filter set"
        my_thumb :

            # adjust the image quality to 75%
            quality : 75

            # list of transformations to apply (the "filters")
            filters :

                # create a thumbnail: set size to 120x90 and use the "outbound" mode
                # to crop the image when the size ratio of the input differs
                thumbnail  : { size : [120, 90], mode : outbound }

                # create a 2px black border: center the thumbnail on a black background
                # 4px larger to create a 2px border around the final image
                background : { size : [124, 94], position : center, color : '#000000' }
```

You've now created a *filter set* called `my_thumb` that performs a thumbnail
transformation. The `thumbnail` filter sizes the image to the desired width
and height (in this example, 120x90px), and its `mode: outbound` option causes
the resulting image to be cropped if the input ratio differs. The `background`
filter results in a 2px black border by creating a black canvas 124x94px in size,
and positioning the thumbnail at its center.

**Note:**
*A *filter set* can have any number of *filters* defined for it. Simple
transformations may only require a single *filter* while complex
transformations can have an unlimited number of *filters* defined for them.*

There are a number of additional [filters](http://symfony.com/doc/master/bundles/LiipImagineBundle/filters.html),
but for now you can use your newly defined ``my_thumb`` *filter set* immediately
within a template.

*For Twig-based template, use:*

```twig
<img src="{{ '/relative/path/to/image.jpg' | imagine_filter('my_thumb') }}" />
```

*Or, for PHP-based template, use:*

```php
<img src="<?php $this['imagine']->filter('/relative/path/to/image.jpg', 'my_thumb') ?>" />
```

Behind the scenes, the bundle applies the filter(s) to the image on-the-fly
when the first page request is served. The transformed image is then cached
for subsequent requests. The final cached image path would be similar to
`/media/cache/my_thumb/relative/path/to/image.jpg`.

**Note:**
*Using the ``dev`` environment you might find that images are not properly
rendered via the template helper. This is often caused by having
`intercept_redirect` enabled in your application configuration. To ensure
images are rendered, it is strongly suggested to disable this option:

```yml
# app/config/config_dev.yml

web_profiler :
    intercept_redirects : false
```


### Runtime Options

Sometime, you may have a filter defined that fulfills 99% of your usage
scenarios. Instead of defining a new filter for the erroneous 1% of cases,
you may instead choose to alter the behavior of a filter at runtime by
passing the template helper an options array.

*For Twig-based template, use:*

```twig
{% set runtimeConfig = {"thumbnail": {"size": [50, 50] }} %}

<img src="{{ '/relative/path/to/image.jpg' | imagine_filter('my_thumb', runtimeConfig) }}" />
```

*Or, for PHP-based template, use:*

```php
<?php
$runtimeConfig = array(
    "thumbnail" => array(
        "size" => array(50, 50)
    )
);
?>

<img src="<?php $this['imagine']->filter('/relative/path/to/image.jpg', 'my_thumb', $runtimeConfig) ?>" />
```


### Path Resolution

Sometime you need to resolve the image path returned by this bundle for a
filtered image. This can easily be achieved using Symfony's console binary
or pragmatically from within a controller or other piece of code.


#### Resolve with the Console

You can resolve an image URL using the console command
`liip:imagine:cache:resolve`. The only required argument is one or more
relative image paths (which must be separated by a space).

```bash
$ php app/console liip:imagine:cache:resolve relative/path/to/image1.jpg relative/path/to/image2.jpg
```

Additionally, you can use the ``--filters`` option to specify which filter
you want to resolve for (if the ``--filters`` option is omitted, all
available filters will be resolved).

```bash
$ php app/console liip:imagine:cache:resolve relative/path/to/image1.jpg --filters=my_thumb
```


#### Resolve Pragmatically

You can resolve the image URL in your code using the `getBrowserPath`
method of the `liip_imagine.cache.manager` service. Assuming you already
have the service assigned to a variable called `$imagineCacheManager`,
you would run:

```php
$imagineCacheManager->getBrowserPath('/relative/path/to/image.jpg', 'my_thumb');
```

Often, you need to perform this operation in a controller. Assuming your
controller inherits from the base Symfony controller, you can take advantage
of the inherited ``get`` method to request the ``liip_imagine.cache.manager``
service, from which you can call ``getBrowserPath`` on a relative image
path to get its resolved location.

```php
/** @var CacheManager */
$imagineCacheManager = $this->get('liip_imagine.cache.manager');

/** @var string */
$resolvedPath = $this->getBrowserPath('/relative/path/to/image.jpg', 'my_thumb');
```


## Filters

This bundle provides a set of built-in filters andy ou may easily
define your own filters as well. Reference the
[filters chapter](http://symfony.com/doc/master/bundles/LiipImagineBundle/filters.html)
from our documentation.


## Use as a Service

If you need to use your defined "filter sets" from within your controller, you 
can fetch this bundle's controller from the service container and handle 
the response yourself.

```php
<?php

class MyController extends Controller
{
    public function indexAction()
    {
        /** @var ImagineController */
        $imagine = $this
            ->container
            ->get('liip_imagine.controller');

        /** @var RedirectResponse */
        $imagemanagerResponse = $imagine
            ->filterAction(
                $this->request,         // http request
                'uploads/foo.jpg',      // original image you want to apply a filter to
                'my_thumb'              // filter defined in config.yml
            );

        /** @var CacheManager */
        $cacheManager = $this
            ->container
            ->get('liip_imagine.cache.manager');

        /** @var string */
        $sourcePath = $cacheManager
            ->getBrowserPath(
                'uploads/foo.jpg',
                'my_thumb'
            );

        // ..
    }
}

?>
```

If you need to add more logic, the recommended solution is to either
extend `ImagineController.php` or use it as the basis for your own 
implementation.

To use the controller in another service, you have to simulate a new request.

```php
<?php

/** @var ImagineController */
$imagine = $this
    ->container
    ->get('liip_imagine.controller');

/** @var Response */
$response = $imagine
    ->filterAction(
        new Symfony\Component\HttpFoundation\Request(),
        'uploads/foo.jpg',
        'my_thumb'
    );

?>
```


## Images Outside Data Root

When your setup requires your source images reside outside the web root,
you have to set your loader's `data_root` parameter in your configuration 
(often `app/config/config.yml`) with the absolute path to your source image 
location.

```yml
liip_imagine:
    loaders:
        default:
            filesystem:
                data_root: /path/to/source/images/dir
```

This location must be readable by your web server. On a system that supports 
`setfacl` (such as Linux/BSD), use

```sh
HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`

sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX /path/to/source/images/dir

sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX /path/to/source/images/dir
```

See the [Symfony Permissions documentation](http://symfony.com/doc/current/setup/file_permissions.html)
for commands compatible with macOS and other environments.


### Using Apache

You need to grant read access for Apache by adding the following to your 
Apache VHost configuration

```xml
<VirtualHost *:80>
    <!-- Rest of directives like DocumentRoot or ServerName -->

    Alias /FavouriteAlias /path/to/source/images/dir
    <Directory "/path/to/source/images/dir">
        AllowOverride None
        Allow from All
    </Directory>
</VirtualHost>
```

Alternatively, you can place the directive in a separate file within your 
project, and include it within your Apache VHost configuration. For example, 
you can create the file `app/config/apache/photos.xml` and add the following 
to your VHost file

```xml
<VirtualHost *:80>
    <!-- Rest of directives like DocumentRoot or ServerName -->

    Include "/path/to/your/project/app/config/apache/photos.xml"
</VirtualHost>
```

This method keeps the file with the rest of your code, allowing you to change
it easily or create different environment-dependent configuration files.

Once you have configured Apache properly, the relative path to an image with 
the following absolute path `/path/to/source/images/dir/logo.png` must be
`/FavouriteAlias/logo.png`.


## Documentation

For more detailed information about the features of this bundle, refer to
the [documentation](http://symfony.com/doc/master/bundles/LiipImagineBundle).
