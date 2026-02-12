<?php

namespace Reactolith\SymfonyBundle\EventListener;

use Reactolith\SymfonyBundle\Vite\ViteAssetResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ComponentPreloadListener implements EventSubscriberInterface
{
    public function __construct(
        private string $tagPrefix,
        private ?ViteAssetResolver $viteResolver = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $contentType = $response->headers->get('Content-Type', '');

        if ($contentType !== '' && !str_contains($contentType, 'text/html')) {
            return;
        }

        $content = $response->getContent();
        if ($content === false || $content === '') {
            return;
        }

        // Find all custom element tags matching the prefix
        $pattern = '/<' . preg_quote($this->tagPrefix, '/') . '([\w-]+)/';
        preg_match_all($pattern, $content, $matches);

        if (empty($matches[1])) {
            return;
        }

        $components = array_values(array_unique($matches[1]));
        sort($components);

        // Send full tag names as custom header
        $tagNames = array_map(fn (string $c): string => $this->tagPrefix . $c, $components);
        $response->headers->set('X-Reactolith-Components', implode(', ', $tagNames));

        // Add Link preload headers for entry point assets
        if ($this->viteResolver !== null) {
            foreach ($this->viteResolver->getPreloadLinks() as $link) {
                $response->headers->set(
                    'Link',
                    sprintf('<%s>; rel=preload; as=%s', $link['url'], $link['type']),
                    false, // append, don't replace
                );
            }
        }
    }
}
