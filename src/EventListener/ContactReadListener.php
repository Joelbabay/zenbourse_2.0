<?php
// src/EventListener/ContactReadListener.php
namespace App\EventListener;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDetailEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactReadListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityDetailEvent::class => 'onBeforeEntityDetail',
        ];
    }

    public function onBeforeEntityDetail(BeforeEntityDetailEvent $event)
    {
        $entity = $event->getEntityInstance();

        // Vérifie si l'entité est un message de contact
        if (!$entity instanceof Contact) {
            return;
        }

        // Si le message n'a pas encore été lu, on le marque comme lu
        if (!$entity->isRead()) {
            $entity->setRead(true);
            $this->entityManager->flush();
        }
    }
}
