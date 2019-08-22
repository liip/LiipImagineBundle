<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Templating\Helper;

@trigger_error('The '.FilterHelper::class.' class is deprecated since version 2.2 and will be removed in 3.0; use Twig instead.', E_USER_DEPRECATED);

use Liip\ImagineBundle\Templating\FilterTrait;
use Symfony\Component\Templating\Helper\Helper;

class FilterHelper extends Helper
{
    use FilterTrait;
}
