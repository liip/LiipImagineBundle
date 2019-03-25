

Filters
=======

Filters perform image transformation operations. While some filter set
definitions may only require a single filter, more complex definitions
often require many filters. Any number of filters can be chained to
achieve the desired result.

.. tip::

    You may need to define your own filter to meet your application's specific
    requirements. Reference the :ref:`custom filters section <filter-custom>`
    for implementation details.


Built-in filters
----------------

A number of built-in filters are provided to fulfill the majority of common
use-cases.


.. toctree::
    :maxdepth: 2

    filters/sizing
    filters/orientation
    filters/general


.. _filter-custom:

Custom filters
--------------

You can easily define your own, custom filters to perform any image
transformation operations required. Creating a custom filter begins
by creating a class that implements the following interface:

.. code-block:: php

    namespace Liip\ImagineBundle\Imagine\Filter\Loader;

    interface LoaderInterface
    {
        public function load(ImageInterface $image, array $options = []);
    }

As defined in ``LoaderInterface``, the only required method is one named ``load``,
which is provided an instance of ``ImageInterface`` and an array of options, and
subsequently provides an instance of ``ImageInterface`` in return.

The following is a template for creating your own filter. You must provide
the implementation for the ``load`` method to create a valid filter.

.. code-block:: php

    namespace AppBundle\Imagine\Filter\Loader;

    use Imagine\Image\ImageInterface;
    use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

    class MyCustomFilter implements LoaderInterface
    {
        /**
         * @param ImageInterface $image
         * @param array          $options
         *
         * @return ImageInterface
         */
        public function load(ImageInterface $image, array $options = [])
        {
            /** @todo: implement */
        }
    }

After you have finished implementing your custom filter class, it must be defined
as a service in the Symfony Service Container and tagged with ``liip_imagine.filter.loader``.
To register a our filter, ``AppBundle\Imagine\Filter\Loader\MyCustomFilter``, as
``my_custom_filter``, use the following configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        services:
            app.filter.my_custom_filter:
                class: AppBundle\Imagine\Filter\Loader\MyCustomFilter
                tags:
                    - { name: "liip_imagine.filter.loader", loader: my_custom_filter }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="app.filter.my_custom_filter" class="AppBundle\Imagine\Filter\Loader\MyCustomFilter">
            <tag name="liip_imagine.filter.loader" loader="my_custom_filter" />
        </service>

.. note::

    For more information on the Service Container, reference the official
    `Symfony Service Container documentation`_.

You can now reference and use your custom filter when defining filter sets in your configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_special_style:
                filters:
                    my_custom_filter: { }


.. _filter-dynamic:

Dynamic filters
---------------

It is possible to dynamically modify the configuration that will be applied
to the image, by passing configuration as third parameter to ``applyFilter``:

.. code-block:: php

    namespace App\Service;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Liip\ImagineBundle\Imagine\Cache\CacheManager;
    use Liip\ImagineBundle\Imagine\Data\DataManager;
    use Liip\ImagineBundle\Imagine\Filter\FilterManager;

    class ImageService
    {
        private $cacheManager;
        private $dataManager;
        private $filterManager;

        public function __construct(CacheManager $cacheManager, DataManager $dataManager, FilterManager $filterManager) {
            $this->cacheManager  = $cacheManager;
            $this->dataManager   = $dataManager;
            $this->filterManager = $filterManager;
        }

        public function filter(int $width, int $height) {
            $filter = '...'; // Name of the `filter_set` in `config/packages/liip_imagine.yaml`
            $path = '...'; // Path of the image, relative to `/public/`
            
            if (!$this->cacheManager->isStored($path, $filter)) {
                $binary = $this->dataManager->find($filter, $path);

                $filteredBinary = $this->filterManager->applyFilter($binary, $filter, [
                    'filters' => [
                        'thumbnail' => [
                            'size' => [$width, $height]
                        ]
                    ]
                ]);

                $this->cacheManager->store($filteredBinary, $path, $filter);
            }
            return new RedirectResponse($this->cacheManager->resolve($path, $filter), Response::HTTP_MOVED_PERMANENTLY);
        }
    }

.. _`Symfony Service Container documentation`: http://symfony.com/doc/current/book/service_container.html
