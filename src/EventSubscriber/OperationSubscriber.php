<?php
namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Entity\Operation;
use App\Entity\Documents;

class OperationEventSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
        ];
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Operation && $entity->getStatus() === 'Terminée') {
            $em = $args->getObjectManager();

            // Créer un nouveau document (facture)
            $document = new Documents();
            $document->setCustomer($entity->getCustomer());
            $document->setSalarie($entity->getSalarie());
            $document->setOperation($entity);
            $document->setType($entity->getType());
            // Supposant que getUrl() retourne l'URL pour télécharger la facture
            $document->setUrl('url/to/your/pdf');

            $em->persist($document);
            $em->flush();
        }
    }
}
