<?php

namespace Reactolith\SymfonyBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Reactolith\SymfonyBundle\EventListener\ComponentPreloadListener;
use Reactolith\SymfonyBundle\Vite\ViteAssetResolver;
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

    public function testAddsLinkPreloadHeadersWhenViteResolverProvided(): void
    {
        // Create a temp directory with a Vite manifest
        $tmpDir = sys_get_temp_dir() . '/reactolith-preload-test-' . uniqid();
        mkdir($tmpDir . '/public/build/.vite', 0777, true);
        file_put_contents($tmpDir . '/public/build/.vite/manifest.json', json_encode([
            'resources/js/app.js' => [
                'file' => 'assets/app-abc.js',
                'isEntry' => true,
                'css' => ['assets/app-def.css'],
            ],
        ]));

        $resolver = new ViteAssetResolver($tmpDir, [
            'build_directory' => 'build',
            'entry_points' => ['resources/js/app.js'],
            'dev_server_url' => null,
        ]);

        $listener = new ComponentPreloadListener('ui-', $resolver);

        $html = '<ui-button>Click</ui-button>';
        $event = $this->createResponseEvent($html);

        $listener->onKernelResponse($event);

        $linkHeaders = $event->getResponse()->headers->all('link');
        $this->assertContains('</build/assets/app-abc.js>; rel=preload; as=script', $linkHeaders);
        $this->assertContains('</build/assets/app-def.css>; rel=preload; as=style', $linkHeaders);

        // Cleanup
        $this->removeDir($tmpDir);
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

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
