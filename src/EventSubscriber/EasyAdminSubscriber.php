<?php
// src/EventSubscriber/EasyAdminSubscriber.php
namespace App\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onBeforeEntityUpdated(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Operation)) {
            return;
        }

        if (!$this->security->isGranted('EDIT', $entity)) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à modifier cette opération.');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityUpdatedEvent::class => 'onBeforeEntityUpdated',
        ];
    }
}
