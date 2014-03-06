# StreamLoader

## Using factory

liip_imagine:
    loaders:
        stream.profile_photos:
            stream:
                wrapper: gaufrette://profile_photos

## Custom

The `Liip\ImagineBundle\Imagine\Data\Loader\StreamLoader` allows to read images from any stream registered
thus allowing you to serve your images from literally anywhere.

The example service definition shows how to use a stream wrapped by the [Gaufrette](https://github.com/KnpLabs/Gaufrette) filesystem abstraction layer.
In order to have this example working, you need to register the stream wrapper first,
refer to the [Gaufrette README](https://github.com/KnpLabs/Gaufrette/blob/master/README.markdown) on how to do this.

If you are using the [KnpGaufretteBundle](https://github.com/KnpLabs/KnpGaufretteBundle)
you can make use of the [StreamWrapper configuration](https://github.com/KnpLabs/KnpGaufretteBundle#stream-wrapper) to register the filesystems.

``` yaml
services:
    acme.liip_imagine.data.loader.stream.profile_photos:
        class: "%liip_imagine.data.loader.stream.class%"
        arguments:
            - 'gaufrette://profile_photos/'
        tags:
            - { name: 'liip_imagine.data.loader', loader: 'stream.profile_photos' }
```

## Usage

Now you are ready to use the `AwsS3Resolver` by configuring the bundle.
The following example will configure the resolver is default.

``` yaml
liip_imagine:
    data_loader: stream.profile_photos
```

- [Back to data loaders](../data-loaders.md)
- [Back to the index](../index.md)
