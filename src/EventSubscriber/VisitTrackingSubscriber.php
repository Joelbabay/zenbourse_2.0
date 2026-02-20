<?php
// src/EventSubscriber/VisitTrackingSubscriber.php

namespace App\EventSubscriber;

use App\Service\VisitService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class VisitTrackingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private VisitService $visitService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            // IMPORTANT : Priorité basse pour s'exécuter APRÈS l'authentification
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $this->visitService->trackVisit($request);
    }
}
