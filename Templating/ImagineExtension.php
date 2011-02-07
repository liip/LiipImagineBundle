<?php

namespace Bundle\Avalanche\ImagineBundle\Templating;

use Bundle\Avalanche\ImagineBundle\Imagine\FilterManager;
use Imagine\ImagineInterface;
use Imagine\Filter\FilterInterface;

class ImagineExtension extends \Twig_Extension
{
    private $imagine;
    private $filterManager;
    private $webRoot;
    private $cacheDir;

    public function __construct(ImagineInterface $imagine, FilterManager $filterManager, $webRoot, $cacheDir)
    {
        $this->imagine       = $imagine;
        $this->filterManager = $filterManager;
        $this->webRoot       = $webRoot;
        $this->cacheDir      = $cacheDir;

        if (0 !== strpos($this->cacheDir, $this->webRoot)) {
            throw new \InvalidArgumentException('Looks like the specified "cache_dir" is not in the "web_root", please make sure imagine "cache_dir" is publicly accessible');
        }
    }

    public function getFilters()
    {
        return array(
            'apply_filter' => new \Twig_Filter_Method($this, 'applyFilter'),
        );
    }

    public function applyFilter($path, $filter)
    {
        $cacheFile = md5($path);
        $ext       = pathinfo($path, PATHINFO_EXTENSION);
        $cachePath = $this->cacheDir.'/'.$filter.'/'.$cacheFile[0].'/'.$cacheFile[1].'/'.$cacheFile.'.'.$ext;

        if (!file_exists($cachePath)) {
            $filter = $this->filterManager->get($filter);
            $image  = $this->imagine->open($path);

            $filter->apply($image)->save($cachePath);

            unset($filter, $image);
        }

        return strstr($cachePath, $this->webRoot);
    }

    public function getName()
    {
        return 'imagine';
    }
}
