#Requirements#

ImagineBundle requires ["Imagine library"](/avalanche123/Imagine)

#Installation#

 1. Go to the `src` directory of your project

        cd src/

 2. Install Imagine library in your vendor directory

        git clone git://github.com/avalanche123/Imagine.git vendor/imagine

 3. Register Namespaces in `autoload.php`

        $loader->registerNamespaces(array(
            // your libraries
            'Imagine'   => __DIR__.'/src/vendor/imagine/lib',
            'Avalanche' => __DIR__.'/vendor/bundles',
        ));

 4. Clone ImagineBundle into your src directory under Avalanche/Bundle path

        mkdir -pv Avalanche/Bundle
        git clone git://github.com/avalanche123/AvalancheImagineBundle.git Avalanche/Bundle/ImagineBundle

 - Open your kernel and register the bundle

        public function registerBundles()
        {
            // thrid-party bundle
            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
        }

 - Register Imagine dynamic routes in your `routing.yml` or equivalent file

        _imagine:
            resource: .
            type:     imagine

 - Configure the bundle and enjoy

#Configuration#

Enable the bundle extension by adding the following to your `app/config.yml`
    
    avalanche_imagine:
        web_root:     %kernel.root_dir%/../web
        cache_prefix: imagine
        driver:       gd
        filters:
            thumbnail:
                type:    thumbnail
                options: { size: [120, 90], mode: outbound }
    
There are several configuration options available for ImagineBundle:

 - `web_root` - must be the absolute path to you application's web root, this is used to determine where to put generated image files, so that apache will pick them up before handing the request to Symfony2 next time they are requested

    default: %kernel.root_dir%/../web

 - `cache_prefix` - this is also used in the path for image generation, use to not clutter your web root with cache files. E.g. if `imagine` is specified, the images would be written to web root/imagine

    default: imagine

 - `driver` - one of the three 'gd', 'imagick', 'gmagick'

    default: gd

 - `filters` - specify filter aliases and options along with filter loader types to use

Each filter that you specify have the following options:

 - `type` - determine the type of loader to be used, refer to loaders section for more information
 - `options` - required for loaders that need options, can be omitted for others
 - `path` - this is an overload for globally specified `cache_prefix`, to let you direct specific to cache its results at a different location. Generated path would not contain the filter name in this case, only the specified `path` + source image path.

#Loaders#

ImagineBundle let's you define your own `Avalanche\Bundle\ImagineBundle\Imagine\Filter\Loader\LoaderInterface` instance, that must know how to instantiate a needed filter.

Once you have your filter loader class created, you need to register it in the DIC using `imagine.filter.loader` tag with `filter` attribute, that corresponds to `type` attribute in `filters` collection in the bundle configuration
    
    <tag name="imagine.filter.loader" filter="thumbnail" />
    
For an example of filter loader implementation, refer to `Avalanche\Bundle\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader`

ImagineBundle comes with the following filter loaders pre-built:

 - `thumbnail` - has two modes - 'outbound' and 'inset'
    
        filters:
            thumbnail:
                type:    thumbnail
                options: { size: [120, 90], mode: outbound }
    
#Basic Usage#

ImagineBundle uses Imagine to apply filters to images by path dynamically on the first request and cache them in a similar path, so that if mod_rewrite or other alternative rewrite engine is enabled, it would serve the cached image instead of handling request to Symfony2 on all subsequent requests.

##Twig##

Usage in twig is simple:

    <img src="{{ '/relative/path/to/image.jpg'|apply_filter('thumbnail') }}" />

##PHP##

Usage in PHP templates:

    <img src="<?php $this['imagine']->filter('/relative/path/to/image.jpg', 'thumbnail') ?>" />

After the filter is applied, it simply rewrites the path.
The previous examples in standard configuration would be rewritten to `/imagine/thumbnail/relative/path/to/image.jpg`. As you can see its as simple as prefixing the source path with `cache_prefix` and the filter alias.
