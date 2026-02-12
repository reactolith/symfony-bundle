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

    public function testDetectsComponentsAndSetsHeader(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $html = '<html><body><ui-button>Click</ui-button><ui-input type="text" /><ui-button>Again</ui-button></body></html>';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $this->assertSame('ui-button, ui-input', $event->getResponse()->headers->get('X-Reactolith-Components'));
    }

    public function testDeduplicatesComponents(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $html = '<ui-button>A</ui-button><ui-button>B</ui-button><ui-button>C</ui-button>';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $this->assertSame('ui-button', $event->getResponse()->headers->get('X-Reactolith-Components'));
    }

    public function testComponentNamesAreSorted(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $html = '<ui-textarea></ui-textarea><ui-button>A</ui-button><ui-input />';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $this->assertSame('ui-button, ui-input, ui-textarea', $event->getResponse()->headers->get('X-Reactolith-Components'));
    }

    public function testCustomPrefixDetection(): void
    {
        $listener = new ComponentPreloadListener('x-');

        $html = '<x-button>Click</x-button><x-input />';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $this->assertSame('x-button, x-input', $event->getResponse()->headers->get('X-Reactolith-Components'));
    }

    public function testSkipsSubRequests(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $response = new Response('<ui-button>Click</ui-button>', 200, ['Content-Type' => 'text/html']);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, new Request(), HttpKernelInterface::SUB_REQUEST, $response);

        $listener->onKernelResponse($event);

        $this->assertFalse($response->headers->has('X-Reactolith-Components'));
    }

    public function testSkipsNonHtmlResponses(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $response = new Response('{"data": "<ui-button>"}', 200, ['Content-Type' => 'application/json']);
        $event = $this->createResponseEventWithResponse($response);

        $listener->onKernelResponse($event);

        $this->assertFalse($response->headers->has('X-Reactolith-Components'));
    }

    public function testProcessesResponseWithoutContentTypeHeader(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $response = new Response('<ui-button>Click</ui-button>');
        $response->headers->remove('Content-Type');
        $event = $this->createResponseEventWithResponse($response);

        $listener->onKernelResponse($event);

        $this->assertSame('ui-button', $response->headers->get('X-Reactolith-Components'));
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

    public function testDetectsNestedComponents(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $html = '<ui-theme-provider><ui-button>Click</ui-button></ui-theme-provider>';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $this->assertSame('ui-button, ui-theme-provider', $event->getResponse()->headers->get('X-Reactolith-Components'));
    }

    public function testDetectsComponentsWithHyphenatedNames(): void
    {
        $listener = new ComponentPreloadListener('ui-');

        $html = '<ui-router-progress-bar></ui-router-progress-bar><ui-radio-group-item />';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $this->assertSame('ui-radio-group-item, ui-router-progress-bar', $event->getResponse()->headers->get('X-Reactolith-Components'));
    }

    private function createResponseEvent(string $content): ResponseEvent
    {
        return $this->createResponseEventWithResponse(
            new Response($content, 200, ['Content-Type' => 'text/html']),
        );
    }

    private function createResponseEventWithResponse(Response $response): ResponseEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ResponseEvent($kernel, new Request(), HttpKernelInterface::MAIN_REQUEST, $response);
    }
}
