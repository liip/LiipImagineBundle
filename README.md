LiipImagineBundle
=================

This bundle is a fork of AvalancheImagineBundle which provides easy image
manipulation support for Symfony2. The goal of the fork is to make the
code more extensible and as a result applicable for more use cases.

For more details on the reason for the fork see:
https://github.com/avalanche123/AvalancheImagineBundle/pull/25

For example with this bundle the following is possible:

``` jinja
<img src="{{ '/relative/path/to/image.jpg' | imagine_filter('thumbnail') }}" />
```

This will perform the transformation called `thumbnail`, which you can define
to do a number of different things, such as resizing, cropping, drawing,
masking, etc.

This bundle integrates the standalone PHP "[Imagine library](https://github.com/avalanche123/Imagine)".

[![Build Status](https://secure.travis-ci.org/liip/LiipImagineBundle.png)](http://travis-ci.org/liip/LiipImagineBundle)
[![Total Downloads](https://poser.pugx.org/liip/imagine-bundle/downloads.png)](https://packagist.org/packages/liip/imagine-bundle)
[![Latest Stable Version](https://poser.pugx.org/liip/imagine-bundle/v/stable.png)](https://packagist.org/packages/liip/imagine-bundle)


## Installation

In case you are not sure how to install this bundle, see the [installation instructions](http://symfony.com/doc/master/bundles/LiipImagineBundle/installation.html).

### Configuration

After installing the bundle, make sure you add this route to your routing:

``` yaml
# app/config/routing.yml

_liip_imagine:
    resource: "@LiipImagineBundle/Resources/config/routing.xml"
```

For a complete configuration drill-down see [the respective chapter in the documentation](http://symfony.com/doc/master/bundles/LiipImagineBundle/configuration.html).

## Basic Usage

This bundle works by configuring a set of filters and then applying those
filters to images inside a template So, start by creating some sort of filter
that you need to apply somewhere in your application. For example, suppose
you want to thumbnail an image to a size of 120x90 pixels:

``` yaml
# app/config/config.yml

liip_imagine:
    resolvers:
       default:
          web_path: ~

    filter_sets:
        cache: ~
        my_thumb:
            quality: 75
            filters:
                thumbnail: { size: [120, 90], mode: outbound }
```

You've now defined a filter set called `my_thumb` that performs a thumbnail transformation.
We'll learn more about available transformations later, but for now, this
new filter can be used immediately in a template:

``` jinja
<img src="{{ '/relative/path/to/image.jpg' | imagine_filter('my_thumb') }}" />
```

Or if you're using PHP templates:

``` php
<img src="<?php $this['imagine']->filter('/relative/path/to/image.jpg', 'my_thumb') ?>" />
```

Behind the scenes, the bundles applies the filter(s) to the image on the first
request and then caches the image to a similar path. On the next request,
the cached image would be served directly from the file system.

In this example, the final rendered path would be something like
`/media/cache/my_thumb/relative/path/to/image.jpg`. This is where Imagine
would save the filtered image file.

You can also pass some options at runtime:

``` jinja
{% set runtimeConfig = {"thumbnail": {"size": [50, 50] }} %}
<img src="{{ '/relative/path/to/image.jpg' | imagine_filter('my_thumb', runtimeConfig) }}" />
```

Or if you're using PHP templates:

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
Also you can resolve image url from console:
```jinja
app/console liip:imagine:cache:resolve relative/path/to/image.jpg relative/path/to/image2.jpg --filters=my_thumb --filters=thumbnail_default
```
Where only paths required parameter. They are separated by space. If you omit filters option will be applied all available filters.

If you need to access filtered image URL in your controller:

``` php
$this->get('liip_imagine.cache.manager')->getBrowserPath('/relative/path/to/image.jpg', 'my_thumb', true),
```

In this case, the final rendered path would contain some random data in the path
`/media/cache/my_thumb/S8rrlhhQ/relative/path/to/image.jpg`. This is where Imagine
would save the filtered image file.

Note: Using the ``dev`` environment you might find that the images are not properly rendered when
using the template helper. This is likely caused by having ``intercept_redirect`` enabled in your
application configuration. To ensure that the images are rendered disable this option:

``` jinja
web_profiler:
    intercept_redirects: false
```

## Filters

The LiipImagineBundle provides a set of built-in filters.
You may easily roll your own filter, see [the filters chapter in the documentation](http://symfony.com/doc/master/bundles/LiipImagineBundle/filters.html).

## Using the controller as a service

If you need to use the filters in a controller, you can just load `ImagineController.php` controller as a service and handle the response:

``` php
class MyController extends Controller
{
    public function indexAction()
    {
        // RedirectResponse object
        $imagemanagerResponse = $this->container
            ->get('liip_imagine.controller')
            ->filterAction(
                $this->request,         // http request
                'uploads/foo.jpg',      // original image you want to apply a filter to
                'my_thumb'              // filter defined in config.yml
            );

        // string to put directly in the "src" of the tag <img>
        $cacheManager = $this->container->get('liip_imagine.cache.manager');
        $srcPath = $cacheManager->getBrowserPath('uploads/foo.jpg', 'my_thumb');

        // ..
    }
}
```

In case you need to add more logic the recommended solution is to either extend `ImagineController.php` controller or take the code from that controller as a basis for your own controller.

If you want to use the service in another service, you have to simulate a new request:

``` php
$imagemanagerResponse = $this->container
    ->get('liip_imagine.controller')
        ->filterAction($this->container->get('request'), 'uploads/foo.jpg', 'my_thumb');
```

## Outside the web root

When your setup requires your source images to live outside the web root, or if that's just the way you roll,
you have to set your loader's parameter `data_root` in the `config.yml` with the absolute path where your source images are
located:

``` yaml
liip_imagine:
    loaders:
        default:
            filesystem:
                data_root: /path/to/source/images/dir
```

Afterwards, you need to grant read access on Apache to access the images source directory. For achieving it you have
to add the following directive to your project's vhost file:

``` xml
<VirtualHost *:80>
    <!-- Rest of directives like DocumentRoot or ServerName -->

    Alias /FavouriteAlias /path/to/source/images/dir
    <Directory "/path/to/source/images/dir">
        AllowOverride None
        Allow from All
    </Directory>
</VirtualHost>
```

Another way would be placing the directive in a separate file living inside your project. For instance,
you can create a file `app/config/apache/photos.xml` and add to the project's vhost the following directive:

``` xml
<VirtualHost *:80>
    <!-- Rest of directives like DocumentRoot or ServerName -->

    Include "/path/to/your/project/app/config/apache/photos.xml"
</VirtualHost>
```

This way you keep the file along with your code and you are able to change your files directory access easily or create
different environment-dependant configuration files.

Either way, once you have granted access on Apache to read the `data_root` files, the relative path of an image with this
absolute path `/path/to/source/images/dir/logo.png` must be `/FavouriteAlias/logo.png` to be readable.

## Documentation

For more detailed information about the features of this bundle, please refer to [the documentation](http://symfony.com/doc/master/bundles/LiipImagineBundle/index.html).
