<?php
// src/EventSubscriber/LogoutEventListener.php

namespace App\EventSubscriber;

use App\Service\LogsService;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class LogoutEventListener implements EventSubscriberInterface
{
    private LogsService $logsService;

    public function __construct(LogsService $logsService)
    {
        $this->logsService = $logsService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
            AuthenticationSuccessEvent::class => 'onAuthenticationSuccessEvent',
            LoginFailureEvent::class => 'onLoginFailureEvent'
        ];
    }

    public function onLogoutEvent(LogoutEvent $event): void
    {
        $user = $event->getToken()->getUser();

        try {
            //Send logs using LogsService
            $logData = [
                'loggerName' => 'Logout',
                'user' => 'N\C',
                'message' => 'User logged out',
                'level' => 'info'
            ];
            $this->logsService->postLog($logData);
        } catch (\Exception $e) {
        }
    }

    public function onAuthenticationSuccessEvent(AuthenticationSuccessEvent $event): void
    {
        // Get user information from the authentication success event
        $user = $event->getAuthenticationToken()->getUser();

        try {
            $logData = [
                'loggerName' => 'Login',
                'user' => 'N\C',
                'message' => 'User authentication successful',
                'level' => 'info'
            ];
            $this->logsService->postLog($logData);
        } catch (\Exception $e) {
        }
    }

    public function onLoginFailureEvent(LoginFailureEvent $event): void
    {
        try {
            $logData = [
                'loggerName' => 'Login',
                'user' => 'N\C',
                'message' => 'User failed to log',
                'level' => 'error'
            ];
            $this->logsService->postLog($logData);
        } catch (\Exception $e) {
        }
    }
}

