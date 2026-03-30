<?php

namespace App\Service;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createNotification(string $message): void
    {
        $notification = new Notification();
        $notification->setMessage($message);
        
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function getUnreadNotifications(): array
    {
        return $this->entityManager->getRepository(Notification::class)
            ->findBy(['isRead' => false], ['createdAt' => 'DESC']);
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->entityManager->flush();
    }
} 