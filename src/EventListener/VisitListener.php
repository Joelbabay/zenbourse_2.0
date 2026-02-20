<?php

namespace App\EventListener;

use App\Service\VisitService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: -10)]
final class VisitListener
{
    public function __construct(
        private VisitService $visitService
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        // Ne traiter que les requêtes principales (pas les sous-requêtes)
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Tracker la visite
        $this->visitService->trackVisit($request);
    }
}
