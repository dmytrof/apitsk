<?php

namespace App\EventSubscriber;

use App\Exception\RequestContentParserException;
use App\Utils\RequestContentParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestContentParserSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['parseRequestContentToArray', 100],
            ],
        ];
    }

    /**
     * @param ControllerEvent $event
     */
    public function parseRequestContentToArray(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->getContent() || !in_array($request->getMethod(), ['PUT','PATCH'])) {
            return;
        }

        try {
            $params = (new RequestContentParser())->parseContent($request->getContent());
            $request->request->add($params ?: []);
        } catch (RequestContentParserException $e) {
        }
    }
}