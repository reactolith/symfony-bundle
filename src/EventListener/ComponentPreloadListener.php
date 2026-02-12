<?php

namespace Reactolith\SymfonyBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ComponentPreloadListener implements EventSubscriberInterface
{
    public function __construct(
        private string $tagPrefix,
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

        $pattern = '/<' . preg_quote($this->tagPrefix, '/') . '([\w-]+)/';
        preg_match_all($pattern, $content, $matches);

        if (empty($matches[1])) {
            return;
        }

        $components = array_values(array_unique($matches[1]));
        sort($components);

        $tagNames = array_map(fn (string $c): string => $this->tagPrefix . $c, $components);
        $response->headers->set('X-Reactolith-Components', implode(', ', $tagNames));
    }
}
