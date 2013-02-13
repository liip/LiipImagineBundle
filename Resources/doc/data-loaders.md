# Built-In DataLoader

* [DoctrinePHPCR](data-loader/doctrine-phpcr.md)
* [MongoDB GridFS](data-loader/gridfs.md)
* [Stream](data-loader/stream.md)

# Custom image loaders

The ImagineBundle allows you to add your custom image loader classes. The only
requirement is that each data loader implement the following interface:

    Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface

To tell the bundle about your new data loader, register it in the service
container and apply the `liip_imagine.data.loader` tag to it (example here in XML):

``` xml
<service id="acme_imagine.data.loader.my_custom" class="Acme\ImagineBundle\Imagine\Data\Loader\MyCustomDataLoader">
    <tag name="liip_imagine.data.loader" loader="my_custom_data" />
    <argument type="service" id="liip_imagine" />
    <argument>%liip_imagine.formats%</argument>
</service>
```

For more information on the service container, see the Symfony2
[Service Container](http://symfony.com/doc/current/book/service_container.html) documentation.

You can set your custom data loader by adding it to the your configuration as the new
default loader as follows:

``` yaml
liip_imagine:
    data_loader: my_custom_data
```

Alternatively you can only set the custom data loader for just a specific filter set:

``` yaml
liip_imagine:
    filter_sets:
        my_special_style:
            data_loader: my_custom_data
            filters:
                my_custom_filter: { }
```


For an example of a data loader implementation, refer to
`Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader`.

## Extending the image loader with data transformers

You can extend a custom data loader to support virtually any file type using transformers.
A data tranformer is intended to transform a file before actually rendering it. You
can refer to `Liip\ImagineBundle\Imagine\Data\Loader\ExtendedFileSystemLoader` and
to `Liip\ImagineBundle\Imagine\Data\Transformer\PdfTransformer` as an example.

ExtendedFileSystemLoader extends FileSystemLoader and takes, as argument, an array of transformers.
In the example, when a file with the pdf extension is passed to the data loader,
PdfTransformer uses a php imagick object (injected via the service container)
to extract the first page of the document and returns it to the data loader as a png image.

To tell the bundle about the transformers, you have to register them as services
with the new loader:

```yml
services:
    imagick_object:
        class:   Imagick
    acme_custom_transformer:
        class:     Acme\ImagineBundle\Imagine\Data\Transformer\MyCustomTransformer
        arguments:
            -    '@imagick_object'
    custom_loader:
        class:     Acme\ImagineBundle\Imagine\Data\Loader\MyCustomDataLoader
        tags:
            -    { name: liip_imagine.data.loader, loader: custom_data_loader }
        arguments:
            -    '@liip_imagine'
            -    %liip_imagine.formats%
            -    %liip_imagine.data_root%
            -    [ '@acme_custom_transformer' ]
```

Now you can use your custom data loader, with its transformers, setting it as in the previous section.

[Back to the index](index.md)
