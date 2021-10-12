Resolve cache images in background
==================================

By default the LiipImagineBundle processes the image on demand.
It does in resolve controller and saves the result, does a 301 redirect to the processed static image.
The approach has its benefits.
The most notable is simplicity.
Though there are some disadvantages:

* It takes huge amount of time during the first request since we have to do a lot of things.
* The images are processed by web servers. It increases the overall load on them and may affect the site performance.
* The resolve controller url is different from the cached image one.
  If there is nothing in the cache the page will contain the url to resolve controller.
  The varnish may cache the page with those links to the resolve controller.
  A browser keeps sending requests to it though there is no need for it after the first call.
To prepare the cached images in advance, the LiipImagineBundle allows you to use a message queue to have a worker warm up the cache asynchronously. Your application has to send messages about the images as it becomes aware of them (file upload, import processes, ...) and you need to run the worker for the message queue.

Symfony Messenger
-----------------

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

At this step we instruct LiipImagineBundle to load some extra stuff required to process images in background.

.. code-block:: yaml

    # config/packages/liip_imagine.yaml

    liip_imagine:
        messenger: true

Step 3: Run consumers
~~~~~~~~~~~~~~~~~~~~~

Before we can start using it we need a pool of consumers (at least one) to be working in background.
Here's how you can run it:

.. code-block:: terminal

    $ php bin/console messenger:consume liip_imagine --time-limit=3600 --memory-limit=256M

    # use -vv to see details about what's happening
    $ php bin/console messenger:consume liip_imagine --time-limit=3600 --memory-limit=256M -vv

Step 4: Send warmup cache message
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You have to dispatch a message in order to process images in background.
The message must contain the original image path (in terms of LiipImagineBundle).
If you do not define filters, the background process will warmup cache for all available filters.
If the cache already exists, the background process does not recreate it by default.
You can force cache to be recreated and in this case the cached image is removed and a new one replaces it.

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

Enqueue
-------------------

The bundle provides a solution. It utilize messaging pattern and works on top of `enqueue library`_.


Step 1: Install EnqueueBundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, we have to `install EnqueueBundle`_. You have to basically use composer to install the bundle,
register it to AppKernel and adjust settings. Here's the most simplest configuration without any extra dependencies.
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

    $ ./app/console enqueue:consume --setup-broker -vvv

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


.. _`install symfony/messenger`: https://symfony.com/doc/current/messenger.html#installation
.. _`enqueue library`: https://github.com/php-enqueue/enqueue-dev
.. _`install EnqueueBundle`: https://github.com/php-enqueue/enqueue-dev/blob/master/docs/bundle/quick_tour.md
.. _`filesystem transport`: https://github.com/php-enqueue/enqueue-dev/blob/master/docs/transport/filesystem.md