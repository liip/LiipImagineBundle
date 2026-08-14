<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class FormatNegotiator
{
    /**
     * @var array
     */
    private $mimeMap;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(array $mimeMap = [], ?LoggerInterface $logger = null)
    {
        $this->mimeMap = $mimeMap;
        $this->logger = $logger;
    }

    /**
     * Negotiate the best format based on the Request's Accept header and configured alternative formats.
     *
     * @param array $configuredAlternativeFormats Configuration from alternative_formats
     *
     * @return string[] Sorted array of format names (e.g., ['avif', 'webp'])
     */
    public function negotiate(Request $request, array $configuredAlternativeFormats): array
    {
        $acceptedFormats = $this->getAcceptedFormats($request);

        if (empty($acceptedFormats)) {
            return [];
        }

        $negotiated = [];
        foreach ($configuredAlternativeFormats as $format => $config) {
            if (isset($config['generate']) && false === $config['generate']) {
                continue;
            }

            if ($this->isFormatAccepted($format, $request)) {
                $q = $this->getMaxQForFormat($format, $request);
                $priority = $config['priority'] ?? 0;
                $negotiated[] = [
                    'format' => $format,
                    'q' => $q,
                    'priority' => $priority,
                ];
            }
        }

        // Sort by q-factor (desc), then by priority (desc), then by original order
        usort($negotiated, function ($a, $b) {
            if ($a['q'] !== $b['q']) {
                return $b['q'] <=> $a['q'];
            }

            if ($a['priority'] !== $b['priority']) {
                return $b['priority'] <=> $a['priority'];
            }

            return 0;
        });

        return array_column($negotiated, 'format');
    }

    /**
     * Check if a specific format is accepted by the client.
     */
    public function isFormatAccepted(string $format, Request $request): bool
    {
        $mimeTypes = $this->getMimeTypesForFormat($format);
        $acceptHeader = $request->headers->get('Accept', '');

        foreach ($mimeTypes as $mimeType) {
            if (preg_match('#'.preg_quote($mimeType, '#').'(;q=([0-9\.]+))?#', $acceptHeader, $matches)) {
                $q = isset($matches[2]) ? (float) $matches[2] : 1.0;
                if ($q > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns an array of accepted formats with their q-factors.
     */
    public function getAcceptedFormats(Request $request): array
    {
        $acceptHeader = $request->headers->get('Accept', '');
        if (!$acceptHeader) {
            return [];
        }

        $accepted = [];
        // Very basic parsing of Accept header
        $parts = explode(',', $acceptHeader);
        foreach ($parts as $part) {
            $subParts = explode(';', trim($part));
            $mimeType = trim($subParts[0]);
            $q = 1.0;
            if (isset($subParts[1]) && str_starts_with(trim($subParts[1]), 'q=')) {
                $q = (float) mb_substr(trim($subParts[1]), 2);
            }

            foreach ($this->mimeMap as $format => $mimes) {
                if (\in_array($mimeType, (array) $mimes, true)) {
                    $accepted[$format] = max($accepted[$format] ?? 0, $q);
                }
            }
        }

        arsort($accepted);

        return $accepted;
    }

    public function registerMimeTypes(string $format, array $mimeTypes): void
    {
        $this->mimeMap[$format] = $mimeTypes;
    }

    /**
     * Get the max q-factor for a given format from the Accept header.
     */
    private function getMaxQForFormat(string $format, Request $request): float
    {
        $mimeTypes = $this->getMimeTypesForFormat($format);
        $acceptHeader = $request->headers->get('Accept', '');
        $maxQ = 0.0;

        foreach ($mimeTypes as $mimeType) {
            if (preg_match('#'.preg_quote($mimeType, '#').'(;q=([0-9\.]+))?#', $acceptHeader, $matches)) {
                $q = isset($matches[2]) ? (float) $matches[2] : 1.0;
                if ($q > $maxQ) {
                    $maxQ = $q;
                }
            }
        }

        return $maxQ;
    }

    private function getMimeTypesForFormat(string $format): array
    {
        return (array) ($this->mimeMap[$format] ?? []);
    }
}
