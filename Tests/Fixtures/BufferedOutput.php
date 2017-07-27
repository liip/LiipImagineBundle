<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Fixtures;

use Symfony\Component\Console\Output\Output;

class BufferedOutput extends Output
{
    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @return string
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        $messages = (array) $messages;

        foreach ($messages as $message) {
            $this->doWrite($message, $newline);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        $this->buffer .= $message.($newline ? PHP_EOL : '');
    }
}
