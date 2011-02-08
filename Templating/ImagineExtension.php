<?php

namespace Bundle\Avalanche\ImagineBundle\Templating;

use Bundle\Avalanche\ImagineBundle\Imagine\FilterManager;
use Imagine\ImagineInterface;
use Imagine\Filter\FilterInterface;
use Symfony\Bundle\FrameworkBundle\Util\Filesystem;

class ImagineExtension extends \Twig_Extension
{
    private $imagine;
    private $filterManager;
    private $filesystem;
    private $webRoot;
    private $cacheDir;

    public function __construct(ImagineInterface $imagine, FilterManager $filterManager, Filesystem $filesystem, $webRoot, $cacheDir)
    {
        $this->imagine       = $imagine;
        $this->filterManager = $filterManager;
        $this->filesystem    = $filesystem;
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
            if (0 === strpos($path, '/')) {
                $path = $this->webRoot . $path;
            }

            $image  = $this->imagine->open($path);

            $dir = dirname($cachePath);
            if (!is_dir($dir)) {
                $this->filesystem->mkdirs($dir);
            }

            $filter->apply($image)->save($cachePath);

            unset($filter, $image);
        }
        // TODO: find a better way to remove webroot absolute path from produced path
        return str_replace($this->webRoot, "", $cachePath);
    }

    public function getName()
    {
        return 'imagine';
    }
}
