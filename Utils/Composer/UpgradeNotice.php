<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Utils\Composer;

use Composer\Script\Event;

final class UpgradeNotice
{
    /**
     * @param Event $event
     */
    final static public function doWrite(Event $event)
    {
        $package = $event->getComposer()->getPackage();
        $version = substr($package->getVersion(), 0, 5);
        $console = new ConsoleIO($event->getIO(), $package);

        if (false === version_compare(PHP_VERSION, '5.4', '>=')
         || false === $notice = static::getUpgradeNotice($version)) {
            return;
        }

        $console->write('<error> Update Notice: %s </error>', array($version));

        foreach ($notice as $line) {
            $console->write($line);
        }

        if ($console->isInteractive()) {
            $console->ask('Press [ENTER] to acknowledge the above upgrade notice...');
        }
    }

    /**
     * Read in updates file and parse for the passed package version.
     *
     * @param string $version
     *
     * @return string[]|false
     */
    final static private function getUpgradeNotice($version)
    {
        $lines = array_map(function ($line) {
            return preg_replace('{^[\s]{2}}', '', rtrim($line));
        }, static::readUpgradeFile());

        $lines = array_filter($lines, function ($line) use ($version) {
            return static::filterLine($line, $version);
        });

        array_shift($lines);

        return 0 === count($lines) ? false : $lines;
    }

    /**
     * Keep lines if header matches package version, up until the next header is reached.
     *
     * @param string $line
     * @param string $version
     *
     * @return bool
     */
    final static private function filterLine($line, $version)
    {
        static $keepLine = false;

        if (0 === strpos($line, '##')) {
            preg_match_all('{(?<version>[0-9]+\.[0-9]+(?:\.[0-9]+)?)}', $line, $matches);

            $keepLine = count(array_filter($matches['version'], function ($v) use ($version) {
                return false !== strpos($version, $v);
            })) > 0;
        }

        return $keepLine;
    }

    /**
     * Attempt to read the file to an array of lines.
     *
     * @return string[]
     */
    final static private function readUpgradeFile()
    {
        if (false !== $lines = @file(__DIR__ . '/../../UPGRADE.md')) {
            return $lines;
        }

        return array();
    }
}
