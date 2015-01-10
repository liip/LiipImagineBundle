GridFSLoader
============

Load your images from `MongoDB GridFS`_.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_special_style:
                data_loader: grid_fs
                filters:
                    my_custom_filter: { }

Add loader to your services:

.. code-block:: xml

    <service id="liip_imagine.binary.loader.grid_fs" class="Liip\ImagineBundle\Binary\Loader\GridFSLoader">
        <tag name="liip_imagine.binary.loader" loader="grid_fs" />
        <argument type="service" id="doctrine.odm.mongodb.document_manager" />
        <argument>Application\ImageBundle\Document\Image</argument>
    </service>

Reference the image by its id:

.. code-block:: jinja

    <img src="{{ image.id | imagine_filter('my_thumb') }}" />

.. _`MongoDB GridFS`: http://docs.mongodb.org/manual/applications/gridfs/
