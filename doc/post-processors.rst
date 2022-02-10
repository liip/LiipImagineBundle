

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


Built-in processors
-------------------

A number of built-in post-processors are provided by default.


Image Optimizers
~~~~~~~~~~~~~~~~

Post-processors of the *image optimizer* classification are intended to reduce the
final image file size, and therefore improve the load performance of your
application's assets.

.. toctree::
    :maxdepth: 1
    :glob:

    post-processors/*


.. _post-processors-custom:

Custom processors
-----------------

You can define your own post-processors to perform any image binary operations
required. Post-processor need to implement the ``PostProcessorInterface``:

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
executable. You may also want to change ``['image/png']`` to the supported mime types
for your custom post-processor.

.. code-block:: php

    namespace App\Service;

    use Liip\ImagineBundle\Binary\BinaryInterface;
    use Liip\ImagineBundle\Model\Binary;
    use Liip\ImagineBundle\Imagine\Filter\PostProcessorInterface;
    use Symfony\Component\Process\Exception\ProcessFailedException;
    use Symfony\Component\Process\ProcessBuilder;

    class MyCustomPostProcessor implements PostProcessorInterface
    {
        public const EXECUTABLE_PATH = '/path/to/your/executable';

        /**
         * @param BinaryInterface $binary
         *
         * @return BinaryInterface
         */
        public function process(BinaryInterface $binary)
        {
            // ensure the passed binary is a png
            if (!in_array(strtolower($binary->getMimeType()), ['image/png'])) {
                return $binary;
            }

            // create a temporary input file
            if (false === $input = tempnam($path = sys_get_temp_dir(), 'custom_')) {
                throw new \Exception(sprintf('Error created tmp file in "%s".', $path));
            }

            // populate temporary file with passed file contents
            file_put_contents($input, $binary->getContent());

            // create a process builder, add the input file as argument
            $pb = new ProcessBuilder([self::EXECUTABLE_PATH]);
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

You need to `configure a service`_ with your custom post-processor and tag it
with ``liip_imagine.filter.post_processor``.

To register ``App\Service\MyCustomPostProcessor`` with the name
``my_custom_post_processor``, you would use the following configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        app.post_processor.my_custom_post_processor:
            class: App\Service\MyCustomPostProcessor
            tags:
                - { name: 'liip_imagine.filter.post_processor', post_processor: 'my_custom_post_processor' }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="app.post_processor.my_custom_post_processor" class="App\Service\MyCustomPostProcessor">
            <tag name="liip_imagine.filter.post_processor" post_processor="my_custom_post_processor" />
        </service>

Now your custom post-processor can be referenced in a filter set using the name
assigned via the ``post_processor`` tag attribute above (in this example,
``my_custom_post_processor``).

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_special_style:
                post_processors:
                    my_custom_post_processor: { }


.. _`configure a service`: http://symfony.com/doc/current/book/service_container.html
