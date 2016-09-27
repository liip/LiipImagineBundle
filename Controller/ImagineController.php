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
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Exception\SignerException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Liip\ImagineBundle\Service\ImagineService;
use Liip\ImagineBundle\Exception\ExceptionInterface;

class ImagineController
{

    /**
     * @var ImagineService
     */
    protected $imagineService;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;


    public function __construct(ImagineService $imagineService, LoggerInterface $logger = null)
    {
        $this->imagineService = $imagineService;
        $this->logger = $logger;
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function filterAction(Request $request, $path, $filter)
    {
        // decoding special characters and whitespaces from path obtained from url
        $path = urldecode($path);
        $resolver = $request->get('resolver');

        try {
            $response = $this->imagineService->filter($path, $filter, $resolver);
        } catch (NonExistingFilterException $e) {
            $message = sprintf('Could not locate filter "%s" for path "%s". Message was "%s"', $filter, $path, $e->getMessage());

            if (null !== $this->logger) {
                $this->logger->debug($message);
            }
            throw new NotFoundHttpException($message, $e);
        } catch (NotLoadableException $e) {
            if ($e->hasDefaultImageUrl()) {
                return new RedirectResponse($e->getDefaultImageUrl());
            }
            throw new NotFoundHttpException('Source image could not be found', $e);
        } catch (ExceptionInterface $e) {
            // TODO: Need advice. Can we show all messages from our exceptions? Probably no
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }

        return new RedirectResponse($response->getUrl(), $response->getHttpStatus());
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string $hash
     * @param string $path
     * @param string $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function filterRuntimeAction(Request $request, $hash, $path, $filter)
    {
        $resolver = $request->get('resolver');
        $filters = $request->query->get('filters', []);

        try {

            if (!is_array($filters)) {
                throw new NotFoundHttpException(sprintf('Filters must be an array. Value was "%s"', $filters));
            }

            $response = $this->imagineService->filterRuntime($filters, $hash, $path, $filter, $resolver); // TODO: arguments order probably isn't very good
            return new RedirectResponse($response->getUrl(), $response->getHttpStatus());
        } catch (NonExistingFilterException $e) {
            $message = sprintf('Could not locate filter "%s" for path "%s". Message was "%s"', $filter, $hash . '/' . $path, $e->getMessage());

            if (null !== $this->logger) {
                $this->logger->debug($message);
            }

            throw new NotFoundHttpException($message, $e);
        } catch (SignerException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (NotLoadableException $e) {
            throw new NotFoundHttpException(sprintf('Source image could not be found for path "%s" and filter "%s"', $path, $filter), $e);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $hash . '/' . $path, $filter, $e->getMessage()), 0, $e);
        }


    }
}
