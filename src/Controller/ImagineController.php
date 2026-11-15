<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Controller;

use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Config\Controller\ControllerConfig;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Imagine\Cache\Helper\PathHelper;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Service\FilterService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImagineController
{
    private FilterService $filterService;

    private DataManager $dataManager;

    private SignerInterface $signer;

    private ControllerConfig $controllerConfig;

    private LoggerInterface $logger;

    public function __construct(
        FilterService $filterService,
        DataManager $dataManager,
        SignerInterface $signer,
        ControllerConfig $controllerConfig,
        ?LoggerInterface $logger = null,
    ) {
        $this->filterService = $filterService;
        $this->dataManager = $dataManager;
        $this->signer = $signer;
        $this->controllerConfig = $controllerConfig;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * This action applies a given filter to a given image, saves the image and redirects the browser to the stored
     * image.
     *
     * The resulting image is cached so subsequent requests will redirect to the cached image instead applying the
     * filter and storing the image again.
     *
     * @throws NotFoundHttpException
     * @throws RuntimeException
     */
    public function filterAction(Request $request, string $path, string $filter): RedirectResponse
    {
        $path = PathHelper::urlPathToFilePath($path);
        $resolver = $request->query->has('resolver') ? $request->query->getString('resolver') : null;

        return $this->createRedirectResponse(function () use ($path, $filter, $resolver, $request) {
            return $this->filterService->getUrlOfFilteredImage(
                $path,
                $filter,
                $resolver,
                $this->isWebpSupported($request)
            );
        }, $path, $filter);
    }

    /**
     * This action applies a given filter -merged with additional runtime filters- to a given image, saves the image and
     * redirects the browser to the stored image.
     *
     * The resulting image is cached so subsequent requests will redirect to the cached image instead applying the
     * filter and storing the image again.
     *
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws RuntimeException
     */
    public function filterRuntimeAction(Request $request, string $hash, string $path, string $filter): RedirectResponse
    {
        $resolver = $request->query->has('resolver') ? $request->query->getString('resolver') : null;
        $path = PathHelper::urlPathToFilePath($path);
        $runtimeConfig = $request->query->all('filters');

        if (true !== $this->signer->check($hash, $path, $runtimeConfig)) {
            throw new BadRequestHttpException(\sprintf('Signed url does not pass the sign check for path "%s" and filter "%s" and runtime config %s', $path, $filter, json_encode($runtimeConfig)));
        }

        return $this->createRedirectResponse(function () use ($path, $filter, $runtimeConfig, $resolver, $request) {
            return $this->filterService->getUrlOfFilteredImageWithRuntimeFilters(
                $path,
                $filter,
                $runtimeConfig,
                $resolver,
                $this->isWebpSupported($request)
            );
        }, $path, $filter, $hash);
    }

    private function createRedirectResponse(\Closure $url, string $path, string $filter, ?string $hash = null): RedirectResponse
    {
        try {
            return new RedirectResponse($url(), $this->controllerConfig->getRedirectResponseCode());
        } catch (NotLoadableException $exception) {
            // this fallback predates the debug mode check, so it keeps applying in debug mode
            // TODO next major version: change to throw exception in debug mode and get rid fo the evenInDebug flag
            if (null !== $response = $this->createDefaultImageResponse($filter, $exception, true)) {
                return $response;
            }

            throw new NotFoundHttpException(\sprintf('Source image for path "%s" could not be found', $path), $exception);
        } catch (NonExistingFilterException $exception) {
            if (null !== $response = $this->createDefaultImageResponse($filter, $exception)) {
                return $response;
            }

            throw new NotFoundHttpException(\sprintf('Requested non-existing filter "%s"', $filter), $exception);
        } catch (RuntimeException $exception) {
            if (null !== $response = $this->createDefaultImageResponse($filter, $exception)) {
                return $response;
            }

            throw new \RuntimeException(vsprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', [$hash ? \sprintf('%s/%s', $hash, $path) : $path, $filter, $exception->getMessage()]), 0, $exception);
        }
    }

    /**
     * Outside of debug mode, an image that can not be generated at all is replaced by the default image rather
     * than by an error page. The exception is logged so that the problem is not silently swallowed.
     *
     * @param bool $evenInDebug Whether to fall back in debug mode too, for the callers that already did so
     */
    private function createDefaultImageResponse(string $filter, \Throwable $exception, bool $evenInDebug = false): ?RedirectResponse
    {
        if (!$evenInDebug && $this->controllerConfig->isDebug()) {
            return null;
        }

        try {
            $defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter);
        } catch (NonExistingFilterException) {
            // a filter that is not configured has no default image of its own, keep the not found response
            return null;
        }

        if (null === $defaultImageUrl) {
            return null;
        }

        $this->logger->warning('Failed to create image for filter "{filter}", falling back to the default image. Message was "{message}"', [
            'filter' => $filter,
            'message' => $exception->getMessage(),
            'exception' => $exception,
        ]);

        return new RedirectResponse($defaultImageUrl);
    }

    private function isWebpSupported(Request $request): bool
    {
        return false !== mb_stripos($request->headers->get('accept', ''), 'image/webp');
    }
}
