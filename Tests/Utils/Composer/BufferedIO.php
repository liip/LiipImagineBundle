<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Utils\Composer;

use Composer\IO\IOInterface;

abstract class BufferedIO implements IOInterface
{
    private $buffer = array();

    public function write($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $this->buffer = array_merge($this->buffer, (array) $messages);
    }

    public function askConfirmation($question, $default = true)
    {
        $this->buffer[] = $question;
    }

    public function getBuffer()
    {
        return $this->buffer;
    }
}