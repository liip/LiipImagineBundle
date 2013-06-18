# Filters

## Built-in Filters

### The `thumbnail` filter

The thumbnail filter, as the name implies, performs a thumbnail transformation
on your image. Configuration looks like this:

``` yaml
liip_imagine:
    filter_sets:
        my_thumb:
            filters:
                thumbnail: { size: [120, 90], mode: outbound }
```

The `mode` can be either `outbound` or `inset`.

There is also an option `allow_upscale` (default: `false`).
By setting `allow_upscale` to `true`, an image which is smaller than 120x90px in the example above will be expanded to the requested size by interpolation of its content.
Without this option, a smaller image will be left as it. This means you may get images that are smaller than the specified dimensions.

### The `relative_resize` filter

The `relative_resize` filter may be used to `heighten`, `widen`, `increase` or
`scale` an image with respect to its existing dimensions. These options directly
correspond to methods on Imagine's `BoxInterface`.

Given an input image sized 50x40 (width, height), consider the following
annotated configuration examples:

``` yaml
liip_imagine:
    filter_sets:
        my_heighten:
            filters:
                relative_resize: { heighten: 60 } # Transforms 50x40 to 75x60
        my_widen:
            filters:
                relative_resize: { widen: 32 }    # Transforms 50x40 to 32x26
        my_increase:
            filters:
                relative_resize: { increase: 10 } # Transforms 50x40 to 60x50
        my_widen:
            filters:
                relative_resize: { scale: 2.5 }   # Transforms 50x40 to 125x100
```

### The `crop` filter

The crop filter, as the name implies, performs a crop transformation
on your image. Configuration looks like this:

``` yaml
liip_imagine:
    filter_sets:
        my_thumb:
            filters:
                crop: { start: [10, 20], size: [120, 90] }
```

### The `strip` filter

The strip filter removes all profiles and comments from your image.
Configuration looks like this:

``` yaml
liip_imagine:
    filter_sets:
        my_thumb:
            filters:
                strip: ~
```

### The `background` filter

The background filter sets a background color for your image, default is white (#FFF).
Configuration looks like this:

``` yaml
liip_imagine:
    filter_sets:
        my_thumb:
            filters:
                background: { color: '#00FFFF' }
```

## Load your Custom Filters

The ImagineBundle allows you to load your own custom filter classes. The only
requirement is that each filter loader implement the following interface:

    Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface

To tell the bundle about your new filter loader, register it in the service
container and apply the `liip_imagine.filter.loader` tag to it (example here in XML):

``` xml
<service id="liip_imagine.filter.loader.my_custom_filter" class="Acme\ImagineBundle\Imagine\Filter\Loader\MyCustomFilterLoader">
    <tag name="liip_imagine.filter.loader" loader="my_custom_filter" />
</service>
```

For more information on the service container, see the Symfony2
[Service Container](http://symfony.com/doc/current/book/service_container.html) documentation.

You can now reference and use your custom filter when defining filter sets you'd
like to apply in your configuration:

``` yaml
liip_imagine:
    filter_sets:
        my_special_style:
            filters:
                my_custom_filter: { }
```

For an example of a filter loader implementation, refer to
`Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader`.

## Dynamic filters

With a custom data loader it is possible to dynamically modify the configuration that will
be applied to the image. Inside the controller you can access the ``FilterConfiguration``
instance, dynamically adjust the filter configuration (for example based on information
associated with the image or whatever other logic you might want) and set it again.

A simple example showing how to change the filter configuration dynamically. This example
is of course "bogus" since hardcoded values could just as well be set in the configuration
but it illustrates the core idea.

``` php
public function filterAction(Request $request, $path, $filter)
{
    $targetPath = $this->cacheManager->resolve($request, $path, $filter);
    if ($targetPath instanceof Response) {
        return $targetPath;
    }

    $image = $this->dataManager->find($filter, $path);

    $filterConfig = $this->filterManager->getFilterConfiguration();
    $config = $filterConfig->get($filter);
    $config['filters']['thumbnail']['size'] = array(300, 100);
    $filterConfig->set($filter, $config);

    $response = $this->filterManager->get($request, $filter, $image, $path);

    if ($targetPath) {
        $response = $this->cacheManager->store($response, $targetPath, $filter);
    }

    return $response;
}
```

[Back to the index](index.md)
