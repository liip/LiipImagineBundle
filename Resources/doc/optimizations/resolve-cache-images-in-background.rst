Resolve cache images in background
==================================

By default, the LiipImagineBundle processes images on demand. When an image is requested that has
not yet been cached with the requested filter set, the controller applies the filters and caches
the result. Then it redirects the client to the generated image file.

This works without any further tooling. There are some important disadvantages however:

* Applying all the filters to an images can take a lot of time and memory;
* The images have to be processed by the web server answering web requests. This increases the load
  on the server and may affect performance;
* The resolve controller URL is different from the cached image URL. When the image needs to be
  generated, the cached HTML page contains the URL to the controller. If you are caching the HTML,
  all clients using the cache are sent to the controller and need to go through the redirect even
  though it would be unnecessary.

To prepare the cached images in advance, the LiipImagineBundle allows you to use a message queue to
run a worker that warms up the cache asynchronously. Your application has to send messages about the
images as it becomes aware of them (file upload, import processes, ...) and you need to run workers
for the message queue.

Symfony Messenger
-----------------

This bundle provides an integration with `Symfony Messenger`_. When enabled, it provides a message
handler to consume warmup messages.

Step 1: Install
~~~~~~~~~~~~~~~

First, `install symfony/messenger`_ with composer:

.. code-block:: terminal

    $ composer require symfony/messenger

.. code-block:: yaml

    # config/packages/messenger.yaml

    framework:
        messenger:
            transports:
                # https://symfony.com/doc/current/messenger.html#transport-configuration
                liip_imagine: '%env(MESSENGER_TRANSPORT_DSN)%'
                sync: 'sync://'

            routing:
                # Route your messages to the transports
                'Liip\ImagineBundle\Message\WarmupCache': liip_imagine

Step 2: Configure LiipImagineBundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

We need to instruct LiipImagineBundle to load the message handler that consumes the warmup
messages and prepares the cached images in a separate process not tied to web requests.

.. code-block:: yaml

    # config/packages/liip_imagine.yaml

    liip_imagine:
        messenger: true

Step 3: Run consumers
~~~~~~~~~~~~~~~~~~~~~

We need to run at least one consumer for the messages:

.. code-block:: terminal

    $ php bin/console messenger:consume liip_imagine --time-limit=3600 --memory-limit=256M

You can run the consumers on a separate machine, as long as it shares the same storage for the
cached images. In a cloud system, you could even scale consumers based on the queue size to get
fast processing without tying up resources that would do nothing most of the time.

Step 4: Send WarmupCache message
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The last step is to let the message consumer know about images that it needs to cache. When we
reference the image in a Twig template, it is too late to use the message system.

Dispatch a message with the original image path (as you would use it in Twig). You may specify
which filter sets to warm up, or leave that out to have the message consumer warm up all available
filter sets.

Existing cached images are by default not replaced. You can force cache recreation. If ``force`` is
set, cached images are recreated. Force is useful if you replace images with new versions that have
the same file name as before.

.. code-block:: php

    <?php

    use Liip\ImagineBundle\Message\WarmupCache;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Messenger\MessageBusInterface;

    class DefaultController extends AbstractController
    {
        public function index(MessageBusInterface $messageBus)
        {
            // warmup all caches
            $messageBus->dispatch(new WarmupCache('the/path/img.png'));

            // warmup specific cache
            $messageBus->dispatch(new WarmupCache('the/path/img.png', ['fooFilter']));

            // force warmup (removes the cache if exists)
            $messageBus->dispatch(new WarmupCache('the/path/img.png', null, true));
        }
    }

Enqueue (deprecated)
--------------------

The `enqueue library`_ integration is deprecated in favor of the Symfony Messenger integration.

Enqueue integration will be removed in the next major version.

Step 1: Install EnqueueBundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, we have to `install EnqueueBundle`_. You have to basically use composer to install the bundle,
register it to AppKernel and adjust settings. Here's the smallest configuration without any extra dependencies.
It is based on `filesystem transport`_.

.. code-block:: yaml

    # app/config/config.yml

    enqueue:
        default:
            transport: 'file://%kernel.root_dir%/../var/queues'
        client: ~

Step 2: Configure LiipImagineBundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

At this step we instruct LiipImagineBundle to load some extra stuff required to process images in background.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        enqueue: true

Step 3: Run consumers
~~~~~~~~~~~~~~~~~~~~~

Before we can start using it we need a pool of consumers (at least one) to be working in background.
Here's how you can run it:

.. code-block:: bash

    $ ./bin/console enqueue:consume --setup-broker -vvv

Step 4: Send resolve cache message
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You have to send a message in order to process images in background.
The message must contain the original image path (in terms of LiipImagineBundle).
If you do not define filters, the background process will resolve cache for all available filters.
If the cache already exists, the background process does not recreate it by default.
You can force cache to be recreated and in this case the cached image is removed and a new one replaces it.

.. code-block:: php

    <?php

    use Enqueue\Client\ProducerInterface;
    use Liip\ImagineBundle\Async\Commands;
    use Liip\ImagineBundle\Async\ResolveCache;
    use Symfony\Component\DependencyInjection\ContainerInterface;

    /**
     * @var ContainerInterface $container
     * @var ProducerInterface $producer
     */
    $producer = $container->get(ProducerInterface::class);

    // resolve all caches
    $producer->sendCommand(Commands::RESOLVE_CACHE, new ResolveCache('the/path/img.png'));

    // resolve specific cache
    $producer->sendCommand(Commands::RESOLVE_CACHE, new ResolveCache('the/path/img.png', array('fooFilter')));

    // force resolve (removes the cache if exists)
    $producer->sendCommand(Commands::RESOLVE_CACHE, new ResolveCache('the/path/img.png', null, true));

    // send command and wait for reply
    $reply = $producer->sendCommand(Commands::RESOLVE_CACHE, new ResolveCache('the/path/img.png', null, true), true);

    $replyMessage = $reply->receive(20000); // wait for 20 sec


.. _`Symfony Messenger`: https://symfony.com/doc/current/messenger.html
.. _`install symfony/messenger`: https://symfony.com/doc/current/messenger.html#installation
.. _`enqueue library`: https://github.com/php-enqueue/enqueue-dev
.. _`install EnqueueBundle`: https://github.com/php-enqueue/enqueue-dev/blob/master/docs/bundle/quick_tour.md
.. _`filesystem transport`: https://github.com/php-enqueue/enqueue-dev/blob/master/docs/transport/filesystem.md
