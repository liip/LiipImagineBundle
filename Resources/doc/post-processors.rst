
Post-Processors
===============

We already know that :doc:`filters <filters>` perform image transformation. This may
leave you wondering how post-processors fit into the runtime. To help illustrate the
difference between filters and post-processors, it is important to highlight the following.

* Filters modify the **image**.
* Post-processors modify the **image binary**.

After all filters have run, the result is an image binary. This is then provided to,
processed by, and returned from all configured post-processors.

.. tip::

    Post-Processors can be safely chained, even if they operate on different mime-types.
    This makes them perfect for image-specific optimisation techniques.


Built-in Post-Processors
------------------------

A number of built-in post-processors are provided by default. The following are of the
"image optimizer" classification. They are intended to reduce image file size, and
therefor improve the load performance of your application's assets.

* `JpegOptim`_
* `OptiPng`_
* `MozJpeg`_
* `PngQuant`_


JpegOptim
~~~~~~~~~

.. _post-processor-jpegoptim:

The ``JpegOptimPostProcessor`` is a built-in post-processor that performs a number of
*lossless* optimizations on *JPEG* encoded images.

To add this post-processor to the filter set created in the
:ref:`thumbnail usage example <usage-create-thumbnails>` use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [120, 90], mode: outbound }
                    background: { size: [124, 94], position: center, color: '#000' }
                post_processors:
                    jpegoptim: { strip_all: true, max: 70, progressive: true }

This configuration enables metadata stripping and progressive JPEG encoding, and sets
a maximum quality factor of 70 for the resulting image binary.

.. note::

    The default executable path is ``/usr/bin/jpegoptim``. If installed elsewhere
    on your system, you must set the ``liip_imagine.jpegoptim.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.jpegoptim.binary : /your/custom/path/to/jpegoptim


Options
*******

This post-processor offers a number of configuration options:

* ``strip_all``: Removes all comments, EXIF markers, and other image metadata.
* ``max``: Sets the maximum image quality factor.
* ``progressive``: Ensures the image uses progressive encoding.


Parameters
**********

Overwriting any of these parameters will change the post-processor's default behavior:

* ``liip_imagine.jpegoptim.stripAll`` (default  ``true``):
  Removes all comments, EXIF markers, and other metadata from the image binary.

* ``liip_imagine.jpegoptim.max`` (default ``null``):
  Assigns the maximum quality factor for the image binary.

* ``liip_imagine.jpegoptim.progressive`` (default ``true``):
  Ensures that progressive encoding is enabled for the image binary.

* ``liip_imagine.jpegoptim.binary`` (default ``string:/usr/bin/jpegoptim``):
  Sets the location of the ``jpegoptim`` executable.

* ``liip_imagine.jpegoptim.tempDir`` (default ``<empty-string>``):
  Sets the directory to store temporary files.

.. tip::

    The value of ``liip_imagine.jpegoptim.tempDir`` can be set to an in-memory mount point
    on supported operating systems, such as ``/run/shm`` on Linux. This will decrease disk
    load and may increase performance.

OptiPng
~~~~~~~

.. _post-processor-optipng:

The ``OptiPngPostProcessor`` is a built-in post-processor that performs a number of
*lossless* optimizations on *PNG* encoded images.

To add this post-processor to the filter set created in the
:ref:`thumbnail usage example <usage-create-thumbnails>` use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [120, 90], mode: outbound }
                    background: { size: [124, 94], position: center, color: '#000' }
                post_processors:
                    optipng: { strip_all: true, level: 5 }

This configuration enables metadata stripping, and sets a maximum optimization factor of 5
for the resulting image binary.

.. note::

    The default executable path is ``/usr/bin/optipng``. If installed elsewhere
    on your system, you must set the ``liip_imagine.optipng.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.optipng.binary : /your/custom/path/to/optipng


Options
*******

This post-processor offers a number of configuration options:

* ``strip_all``: Removes all comments, EXIF markers, and other image metadata.
* ``level``: Sets the image optimization factor.


Parameters
**********

Overwriting any of these parameters will change the post-processor's default behavior:

* ``liip_imagine.optipng.stripAll`` (default  ``true``):
  Removes all comments, EXIF markers, and other metadata from the image binary.

* ``liip_imagine.optipng.level`` (default ``7``):
  Assigns the maximum optimization factor for the image binary.

* ``liip_imagine.optipng.binary`` (default ``string:/usr/bin/optipng``):
  Sets the location of the ``optipng`` executable.

* ``liip_imagine.optipng.tempDir`` (default ``<empty-string>``):
  Sets the directory to store temporary files.

.. tip::

    The value of ``liip_imagine.optipng.tempDir`` can be set to an in-memory mount point
    on supported operating systems, such as ``/run/shm`` on Linux. This will decrease disk
    load and may increase performance.


MozJpeg
~~~~~~~

.. _post-processor-mozjpeg:

The ``MozJpegPostProcessor`` is a built-in post-processor that performs a number of
*safe, lossy* optimizations on *JPEG* encoded images.

To add this post-processor to the filter set created in the
:ref:`thumbnail usage example <usage-create-thumbnails>` use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [120, 90], mode: outbound }
                    background: { size: [124, 94], position: center, color: '#000' }
                post_processors:
                    mozjpeg: { quality: 80 }

This configuration sets a maximum quality factor of 70 for the resulting image binary.

.. note::

    The default executable path is ``/opt/mozjpeg/bin/cjpeg``. If installed elsewhere
    on your system, you must set the ``liip_imagine.mozjpeg.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.mozjpeg.binary : /your/custom/path/to/cjpeg


Options
*******

This post-processor offers the following configuration option:

* ``quality``: Sets the image quality factor.


Parameters
**********

Overwriting any of these parameters will change the post-processor's default behavior:

* ``liip_imagine.mozjpeg.binary`` (default ``/opt/mozjpeg/bin/cjpeg``):
  Sets the location of the ``cjpeg`` executable.


PngQuant
~~~~~~~~

.. _post-processor-pngquant:

The ``PngquantPostProcessor`` is a built-in post-processor that performs a number of
*safe, lossy* optimizations on *PNG* encoded images.

To add this post-processor to the filter set created in the
:ref:`thumbnail usage example <usage-create-thumbnails>` use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [120, 90], mode: outbound }
                    background: { size: [124, 94], position: center, color: '#000' }
                post_processors:
                    pngquant: { quality: "75-85" }

This configuration sets a quality factor range of 75 to 80 for the resulting image binary.

.. note::

    The default executable path is ``/usr/bin/pngquant``. If installed elsewhere
    on your system, you must set the ``liip_imagine.pngquant.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.pngquant.binary  : /your/custom/path/to/pngquant


Options
*******

This post-processor offers the following configuration option:

* ``quality``: Sets the image quality factor range.


Parameters
**********

Overwriting any of these parameters will change the post-processor's default behavior:

* ``liip_imagine.pngquant.binary`` (default ``/usr/bin/pngquant``):
  Sets the location of the ``pnquant`` executable.


Custom Post-Processors
----------------------

.. _post-processors-custom:

Just like filters, you can easily define your own, custom post-processors to
perform any image binary operations required. Creating a custom post-processor
begins by creating a class that implements the ``PostProcessorInterface``
interface, as shown below.

.. code-block:: php

    interface PostProcessorInterface
    {
        public function process(BinaryInterface $binary);
    }

As defined in ``PostProcessorInterface``, the only required method is one named ``process``,
which is provided an instance of ``BinaryInterface`` as its singular parameter, and
subsequently provides an instance of ``BinaryInterface`` in return.

.. tip::

    You may optionally implement ``ConfigurablePostProcessorInterface`` in your
    post-processor to allow it to be configurable.

The following is a template for creating your own post-processor that calls an executable.
You must set the ``EXECUTABLE_PATH`` class constant to the absolute path of the desired
executable. You may also want to change ``array('image/png')`` to the supported mime types
for your custom post-processor.

.. code-block:: php

    namespace AppBundle\Imagine\Filter\PostProcessor;

    use Liip\ImagineBundle\Binary\BinaryInterface;
    use Liip\ImagineBundle\Model\Binary;
    use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
    use Symfony\Component\Process\Exception\ProcessFailedException;
    use Symfony\Component\Process\ProcessBuilder;

    class MyCustomPostProcessor implements PostProcessorInterface
    {
        const EXECUTABLE_PATH = '/path/to/your/executable';

        /**
         * @param BinaryInterface $binary
         *
         * @return BinaryInterface
         */
        public function process(BinaryInterface $binary)
        {
            // ensure the passed binary is a png
            if (!in_array(strtolower($binary->getMimeType()), array('image/png'))) {
                return $binary;
            }

            // create a temporary input file
            if (false === $input = tempnam($path = sys_get_temp_dir(), 'custom_')) {
                throw new \Exception(sprintf('Error created tmp file in "%s".', $path));
            }

            // populate temporary file with passed file contents
            file_put_contents($input, $binary->getContent());

            // create a process builder, add the input file as argument
            $pb = new ProcessBuilder(array(self::EXECUTABLE_PATH));
            $pb->add($input);

            // get a process instance and run it
            $process = $pb->getProcess();
            $process->run();

            // error out if command returned non-zero
            if (0 !== $process->getExitCode()) {
                unlink($input);
                throw new ProcessFailedException($process);
            }

            // retrieve the result
            $result = new Binary(
                file_get_contents($input),
                $binary->getMimeType(),
                $binary->getFormat()
            );

            // remove temporary file
            unlink($input);

            // return the result
            return $result;
        }
    }

Once you have defined your custom post-processor, you must define it as a service and tag it
with ``liip_imagine.filter.post_processor``.

.. note::

    For more information on the Service Container, reference the official
    `Symfony Service Container documentation`_.

To register ``AppBundle\Imagine\Filter\PostProcessor\MyCustomPostProcessor`` with the name
``my_custom_post_processor``, you would use the following configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        app.post_processor.my_custom_post_processor :
            class : AppBundle\Imagine\Filter\PostProcessor\MyCustomPostProcessor
            tags  :
                - { name : 'liip_imagine.filter.post_processor', post_processor : 'my_custom_post_processor' }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="app.post_processor.my_custom_post_processor" class="AppBundle\Imagine\Filter\PostProcessor\MyCustomPostProcessor">
            <tag name="liip_imagine.filter.post_processor" post_processor="my_custom_post_processor" />
        </service>

Now your custom post-processor can be referenced in a filter set using the name
assigned via the ``post_processor`` tag attribute above (in this example,
``my_custom_post_processor``).

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine :
        filter_sets :
            my_special_style :
                post_processors :
                    my_custom_post_processor : { }


.. _`Symfony Service Container documentation`: http://symfony.com/doc/current/book/service_container.html
