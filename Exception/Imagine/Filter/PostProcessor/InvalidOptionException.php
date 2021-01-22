<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Exception\ExceptionInterface;

class InvalidOptionException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message, array $options = [])
    {
        parent::__construct(sprintf('Invalid post-processor configuration provided (%s) with options %s.',
            $message, $this->stringifyOptions($options)));
    }

    private function stringifyOptions(array $options = []): string
    {
        if (0 === \count($options)) {
            return '[]';
        }

        $options = array_map([$this, 'stringifyOptionValue'], $options);

        array_walk($options, function (&$o, $name) {
            $o = sprintf('%s="%s"', $name, $o);
        });

        return sprintf('[%s]', implode(', ', $options));
    }

    /**
     * @param mixed $value
     */
    private function stringifyOptionValue($value): string
    {
        if (is_scalar($value)) {
            return $value;
        }

        return json_encode($value);
    }
}
