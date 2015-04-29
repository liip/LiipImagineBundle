<?php

namespace Liip\ImagineBundle\Tests\Filter\PostProcessor;

use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Process\ExecutableFinder;

abstract class FilterTest extends AbstractTest
{
    protected function findExecutable($name, $serverKey = null)
    {
        if ($serverKey && isset($_SERVER[$serverKey])) {
            return $_SERVER[$serverKey];
        }
        // update the path (emulates logic in ExecutableFinder)
        $paths = array(__DIR__ . '/../../../../node_modules/.bin');
        if ($current = ini_get('open_basedir')) {
            ini_set('open_basedir', $this->ensurePaths($current, $paths));
        } else {
            $varname = getenv('PATH') ? 'PATH' : 'Path';
            putenv(sprintf('%s=%s', $varname, $this->ensurePaths(getenv($varname), $paths)));
        }
        $finder = new ExecutableFinder();
        return $finder->find($name);
    }

    private function ensurePaths($current, array $paths)
    {
        foreach ($paths as $path) {
            if (!preg_match(sprintf('~(^|%s)%s(%1$s|$)~', PATH_SEPARATOR, preg_quote($path, '~')), $current)) {
                $current .= PATH_SEPARATOR.$path;
            }
        }
        return $current;
    }
}
