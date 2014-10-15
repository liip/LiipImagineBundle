<?php

namespace Liip\ImagineBundle\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Liip\ImagineBundle\EventListener\NotLoadableExceptionListener;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

/**
 * Listens to the request and add default image.
 *
 * @author Emilien Bouard <emilien.bouard@gmail.com>
 */
class NotLoadableExceptionListenerTest extends WebTestCase
{
    protected function getResponseEventMock()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->cookies = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $request->server = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $request->attributes = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->headers = $this->getMockBuilder('Symfony\Component\HttpFoundation\ResponseHeaderBag')
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $event->expects($this->any())
            ->method('getException')
            ->will($this->returnValue(new NotLoadableException()));

        return $event;
    }

    public function testHasNotLoadableException()
    {
        $urlGeneratorMock = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGenerator')
            ->disableOriginalConstructor()
            ->getMock();

        $listener = new NotLoadableExceptionListener($urlGeneratorMock, "image.jpg");
        $listener->onKernelException($this->getResponseEventMock());
    }
}
