<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle;

interface ImagineEvents
{
    /**
     * @Event("Liip\ImagineBundle\Events\CacheResolveEvent")
     */
    public const PRE_RESOLVE = 'liip_imagine.pre_resolve';

    /**
     * @Event("Liip\ImagineBundle\Events\CacheResolveEvent")
     */
    public const POST_RESOLVE = 'liip_imagine.post_resolve';

    /**
     * @Event("Liip\ImagineBundle\Events\CacheStoreEvent")
     */
    public const PRE_STORE = 'liip_imagine.pre_store';

    /**
     * @Event("Liip\ImagineBundle\Events\CacheStoreEvent")
     */
    public const POST_STORE = 'liip_imagine.post_store';

    /**
     * @Event("Liip\ImagineBundle\Events\CacheRemoveEvent")
     */
    public const PRE_REMOVE = 'liip_imagine.pre_remove';

    /**
     * @Event("Liip\ImagineBundle\Events\CacheRemoveEvent")
     */
    public const POST_REMOVE = 'liip_imagine.post_remove';
}
