# GridFSLoader

Load your images from [MongoDB GridFS](http://docs.mongodb.org/manual/applications/gridfs/).

``` yaml
liip_imagine:
    filter_sets:
        my_special_style:
            data_loader: grid_fs
            filters:
                my_custom_filter: { }
```

Add loader to your services:

``` xml
<service id="liip_imagine.data.loader.grid_fs" class="Liip\ImagineBundle\Imagine\Data\Loader\GridFSLoader">
    <tag name="liip_imagine.data.loader" loader="grid_fs" />
    <argument type="service" id="liip_imagine" />
    <argument type="service" id="doctrine.odm.mongodb.document_manager" />
    <argument>Application\ImageBundle\Document\Image</argument>
</service>
```

Reference the image by its id:

``` jinja
<img src="{{ image.id | imagine_filter('my_thumb') }}" />
```

- [Back to data loaders](../data-loaders.md)
- [Back to the index](../index.md)
