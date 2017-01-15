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

use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;

final class ConsoleIO
{
    /**
     * @var string
     */
    private static $format = '<fg=yellow>[%s]</> %s';

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var RootPackageInterface
     */
    private $package;

    /**
     * @param IOInterface          $io
     * @param RootPackageInterface $package
     */
    public function __construct(IOInterface $io, RootPackageInterface $package)
    {
        $this->io = $io;
        $this->package = $package;
    }

    /**
     * @return bool
     */
    public function isInteractive()
    {
        return $this->io->isInteractive();
    }

    /**
     * @param string $line
     * @param array  $replacements
     */
    public function write($line, array $replacements = array())
    {
        $line = sprintf(static::$format, $this->package->getName(), $line);

        if (count($replacements) > 0) {
            $line = vsprintf($line, $replacements);
        }

        $this->io->write($line);
    }

    /**
     * @param string $what
     *
     * @return bool
     */
    public function ask($what)
    {
        return $this->io->askConfirmation(sprintf(static::$format, $this->package->getName(), $what), true);
    }
}
