<?php

namespace Reactolith\SymfonyBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\EventListener\ComponentPreloadListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ComponentPreloadListenerTest extends TestCase
{
    public function testSubscribesToKernelResponse(): void
    {
        $events = ComponentPreloadListener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::RESPONSE, $events);
    }

    public function testDetectsComponentTagsAndSetsHeader(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $html = '<html><body><ui-button>Click</ui-button><ui-input type="text" /><ui-button>Again</ui-button></body></html>';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $header = $event->getResponse()->headers->get('X-Reactolith-Components');
        $this->assertSame('ui-button, ui-input', $header);
    }

    public function testSkipsNonHtmlResponses(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $response = new Response('{"data": "<ui-button>"}', 200, ['Content-Type' => 'application/json']);
        $event = $this->createResponseEventWithResponse($response);

        $listener->onKernelResponse($event);

        $this->assertFalse($event->getResponse()->headers->has('X-Reactolith-Components'));
    }

    public function testSkipsEmptyContent(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $event = $this->createResponseEvent('');

        $listener->onKernelResponse($event);

        $this->assertFalse($event->getResponse()->headers->has('X-Reactolith-Components'));
    }

    public function testNoHeaderWhenNoComponentsFound(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $event = $this->createResponseEvent('<html><body><div>Hello</div></body></html>');

        $listener->onKernelResponse($event);

        $this->assertFalse($event->getResponse()->headers->has('X-Reactolith-Components'));
    }

    public function testCustomPrefixDetection(): void
    {
        $listener = new ComponentPreloadListener('x-');

        $html = '<x-button>Click</x-button><x-input />';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $header = $event->getResponse()->headers->get('X-Reactolith-Components');
        $this->assertSame('x-button, x-input', $header);
    }

    public function testComponentNamesAreSorted(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $html = '<ui-textarea></ui-textarea><ui-button>A</ui-button><ui-input />';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $header = $event->getResponse()->headers->get('X-Reactolith-Components');
        $this->assertSame('ui-button, ui-input, ui-textarea', $header);
    }

    private function createResponseEvent(string $content): ResponseEvent
    {
        $response = new Response($content, 200, ['Content-Type' => 'text/html']);

        return $this->createResponseEventWithResponse($response);
    }

    private function createResponseEventWithResponse(Response $response): ResponseEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        return new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
    }
}
