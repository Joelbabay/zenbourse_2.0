<?php

// src/EventSubscriber/EasyAdminSubscriber.php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => ['onAfterEntityPersisted'],
        ];
    }

    public function onAfterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof User) {
            return;
        }

        try {
            // Laissez l'entité être persistée
        } catch (UniqueConstraintViolationException $e) {
            $this->$this->addFlash('danger', 'L\'adresse email est déjà utilisée.');

            // Redirigez ou interrompez la persistance
            $currentRequest = $this->requestStack->getCurrentRequest();
            if ($currentRequest) {
                throw new BadRequestHttpException('L\'adresse email est déjà utilisée.', $e);
            }
        }
    }
}
