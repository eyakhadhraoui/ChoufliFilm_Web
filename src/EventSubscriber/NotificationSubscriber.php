<?php

namespace App\EventSubscriber;

use App\Repository\NotificationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class NotificationSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $notificationRepository;

    public function __construct(Environment $twig, NotificationRepository $notificationRepository)
    {
        $this->twig = $twig;
        $this->notificationRepository = $notificationRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // Ajoute le repository à toutes les vues
        $this->twig->addGlobal('notificationRepository', $this->notificationRepository);
    }
} 