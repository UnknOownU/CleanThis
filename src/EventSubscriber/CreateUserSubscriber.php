<?php

namespace App\EventSubscriber;

use Exception;
use App\Entity\User;
use App\Service\LogsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;

class RegisterEventListener implements EventSubscriberInterface
{
    private LogsService $logsService;
    public function __construct(LogsService $logsService)
    {
        $this->logsService = $logsService;
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterEntityPersistedEvent::class => ['onCreateUser'],
        ];
    }

    public function onCreateUser(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        // if (!($entity instanceof User)) {
        //     return;
        // }

        $role = $entity->getRoles();
        $userId = $entity->getId();
        
        try {
            //Send logs using LogsService
            $logData = [
            'loggerName' => 'Registration',
            'user' => 'Anonymous',
            'message' => 'User registered',
            'level' => 'info',
            'data' => [
                'role' => $role,
                'userId' => $userId
            ]
            ];
            $this->logsService->postLog($logData);
        } catch (Exception $e) {
        }
    }


}