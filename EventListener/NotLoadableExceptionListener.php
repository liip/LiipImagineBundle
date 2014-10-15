<?php

namespace Liip\ImagineBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

class NotLoadableExceptionListener
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $defaultImage;

    /**
     * @param string $defaultImage
     */
    public function __construct(UrlGeneratorInterface $router, $defaultImage)
    {
        $this->router = $router;
        $this->defaultImage = $defaultImage;
    }

    /**
     * Handles the onKernelException event.
     *
     * @param GetResponseForExceptionEvent $event A GetResponseForExceptionEvent instance
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        //check if default image is empty
        if (empty($this->defaultImage) || $event->getRequest()->attributes->get('path') === ltrim($this->defaultImage, '/')) {
            return;
        }

        $e = $event->getException();
    
        //check if has NotFoundHttpException for use getPrevious method
        if (!$e instanceof NotFoundHttpException && !$e instanceof NotLoadableException) {
            return;
        }

        if (!$this->hasNotLoadableException($e)) {
            return;
        }

        //default response
        $response = $this->defaultImage;

        //if has filer apply filter
        if ($filter = $event->getRequest()->attributes->get('filter')) {
            $response = $this->router->generate(
                'liip_imagine_filter', 
                array(
                    'filter' => $filter,
                    'path' => $this->defaultImage
                )
            );
        }

        $event->setResponse(new RedirectResponse($response));
    }

    /**
     * Check if exception has NotLoadableException class
     *
     * @param HttpExceptionInterface $exception
     *
     * @return boolean
     */
    private function hasNotLoadableException($exception)
    {
        if ($exception instanceof NotLoadableException) {
            return true;
        } else if ($exception instanceof HttpExceptionInterface) {
            while ($exception = $exception->getPrevious()) {
                if ($exception instanceof NotLoadableException) {
                    return true;
                }
            }
        }

        return;
    }
}
