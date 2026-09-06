

Events
======

The bundle dispatches the following events around the cache lifecycle:

* ``PRE_RESOLVE`` and ``POST_RESOLVE``, both receiving a CacheResolveEvent.
* ``PRE_STORE`` and ``POST_STORE``, both receiving a CacheStoreEvent.
* ``PRE_REMOVE`` and ``POST_REMOVE``, both receiving a CacheRemoveEvent.

PRE_RESOLVE
-----------

Called before url to the cached filtered image is generated.


POST_RESOLVE
------------

Called after url to the cached filtered image is generated.

PRE_STORE
---------

Called before the filtered image is handed over to its cache resolver. The
CacheStoreEvent gives access to the binary about to be stored, the path it is
stored under, the filter name and the resolver name that was passed to the
cache manager - the latter being ``null`` when the filter default is used.

POST_STORE
----------

Called after the filtered image has been handed over to its cache resolver,
for example to inspect the image that was just written.

PRE_REMOVE
----------

Called before cached images are removed from their cache resolvers. The
CacheRemoveEvent gives access to the paths and the filter names the removal
applies to. An empty list of paths means every cached path of those filters is
removed. With ``webp_generate`` enabled, the paths also contain the ``.webp``
companion of every removed path.

POST_REMOVE
-----------

Called after the cached images have been removed from their cache resolvers.

Example: Signed URLs
--------------------
Here is an implementation example about users media with differents filters and an S3 as IONOS. At the end we will update the url to get a temporary signed url to a private resource:

First, we need to configure the adapters and the filesystem where will be loaded the images. Normally no need to configure where the cached images will be stored, but we will need it in the next step to generate a signed url.

.. code-block:: yaml
    #oneup_flysystem.yaml

    oneup_flysystem:
        adapters:
            # Original image addapter
            user_adapter:
                awss3v3:
                    client: Aws\S3\S3Client
                    bucket: '%env(IONOS_S3_BUCKET_NAME)%'
                    prefix: "users" # Original image location

            # One adapter per filter and the location of the generated images, with the cache_prefix
            user_thumbnail_adapter:
                awss3v3:
                    client: Aws\S3\S3Client
                    bucket: '%env(IONOS_S3_BUCKET_NAME)%'
                    prefix: "cache/user_thumbnail"
            user_medium_adapter:
                awss3v3:
                    client: Aws\S3\S3Client
                    bucket: '%env(IONOS_S3_BUCKET_NAME)%'
                    prefix: "cache/user_medium"

        filesystems:
            user:
                adapter: user_adapter
                mount: user
            userThumbnail:
                adapter: user_thumbnail_adapter
                mount: userThumbnail
            userMedium:
                adapter: user_medium_adapter
                mount: userMedium

To get a cached resource as private we need to configure the acl of the resolver to private, or the generated image will be in public, it's not what we want in this example.

.. code-block:: yaml
    liip_imagine:
        driver: "gd"
        loaders:
            user_loader:
                flysystem:
                    filesystem_service: oneup_flysystem.user_filesystem

        resolvers:
            aws_s3_resolver:
                aws_s3:
                    bucket: '%env(IONOS_S3_BUCKET_NAME)%'
                    client_config:
                        credentials:
                            key: '%env(IONOS_S3_ACCESS_ID)%'
                            secret: '%env(IONOS_S3_ACCESS_SECRET)%'
                        endpoint: '%env(IONOS_S3_ENDPOINT)%'
                        region: '%env(IONOS_S3_REGION)%'
                        version: '%env(IONOS_S3_VERSION)%'
                    acl: private
                    cache_prefix: cache

                    get_options:
                        Scheme: 'https'
                    put_options:
                        CacheControl: 'max-age=86400'
        cache: aws_s3_resolver
            filter_sets:
                cache: ~
                user_thumbnail:
                    cache: aws_s3_resolver
                    quality: 75
                    filters:
                        thumbnail: { size: [ 130, 130 ], mode: outbound }
                    data_loader: user_loader
                user_medium:
                    cache: aws_s3_resolver
                    quality: 75
                    filters:
                        thumbnail: { size: [ 302, 180 ], mode: outbound }
                    data_loader: user_loader

Finally we create a post resolve subscriber to update the url to the private resource location.

.. code-block:: php

    namespace App\EventSubscriber;

    use App\Enum\MediaFilterEnum;
    use App\Repository\MediaRepository;
    use League\Flysystem\FilesystemOperator;
    use Liip\ImagineBundle\Events\CacheResolveEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class LiipImagineFilterSubscriber implements EventSubscriberInterface
    {
        public function __construct(
            private readonly FilesystemOperator $userThumbnailFilesystem,
            private readonly FilesystemOperator $userMediumFilesystem
        )
        {
        }

        public function onPostResolve(CacheResolveEvent $event): void
        {
            $path = $event->getPath();
            $filter = $event->getFilter();

            $date = new \DateTime();
            // We set the expiration in 10 minutes for example.
            $date = $date->add(new \DateInterval('PT10M'));

            if ($filter === MediaFilterEnum::USER_THUMBNAIL->value) {
                    $url = $this->userThumbnailFilesystem->temporaryUrl($path, $date);
            }
            else if ($filter === MediaFilterEnum::USER_MEDIUM->value) {
                    $url = $this->userMediumFilesystem->temporaryUrl($path, $date);
            }

            if (isset($url)) {
                $event->setUrl($url);
            }
        }

        public static function getSubscribedEvents(): array
        {
            return [
                'liip_imagine.post_resolve' => 'onPostResolve'
            ];
        }
    }

Now, you will get a proper signed url to get your private resource.
