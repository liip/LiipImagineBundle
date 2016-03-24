<?php

namespace Liip\ImagineBundle;

interface ImagineEvents
{
    /**
     * @Event("Liip\ImagineBundle\Events\CacheResolveEvent")
     */
    const PRE_RESOLVE = 'liip_imagine.pre_resolve';

    /**
     * @Event("Liip\ImagineBundle\Events\CacheResolveEvent")
     */
    const POST_RESOLVE = 'liip_imagine.post_resolve';
}
