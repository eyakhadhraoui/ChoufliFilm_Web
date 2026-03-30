<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications')]
    public function index(NotificationRepository $notificationRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer uniquement les notifications de type 'ressource'
        $notifications = $notificationRepository->findBy(
            ['type' => 'ressource'], 
            ['createdAt' => 'DESC']
        );

        // Marquer uniquement les notifications de type 'ressource' comme lues
        $entityManager->createQueryBuilder()
            ->update('App\Entity\Notification', 'n')
            ->set('n.isRead', true)
            ->where('n.type = :type')
            ->andWhere('n.isRead = :isRead')
            ->setParameter('type', 'ressource')
            ->setParameter('isRead', false)
            ->getQuery()
            ->execute();

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
            'notificationRepository' => $notificationRepository
        ]);
    }

    #[Route('/mark-notifications-read', name: 'mark_notifications_read', methods: ['POST'])]
    public function markAsRead(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $query = $entityManager->createQuery('
                UPDATE App\Entity\Notification n 
                SET n.isRead = true 
                WHERE n.type = :type 
                AND n.isRead = false
            ');
            
            $query->setParameter('type', 'user');
            $query->execute();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false], 500);
        }
    }
 
#[Route('/get-new-notifications', name: 'get_new_notifications')]
public function getNewNotifications(NotificationRepository $notificationRepository): JsonResponse
{
    // Récupérer les 5 dernières notifications
    $notifications = $notificationRepository->findBy(
        ['type' => 'user'],
        ['createdAt' => 'DESC'],
        20
    );

    // Compter les notifications non lues
    $unreadCount = $notificationRepository->count([
        'type' => 'user',
        'isRead' => false
    ]);

    // Formater les notifications pour JSON
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $formattedNotifications[] = [
            'id' => $notification->getId(),
            'message' => $notification->getMessage(),
            'image' => $notification->getImage(),
            'isRead' => $notification->getIsRead(),
            'createdAt' => $notification->getCreatedAt()->format('d/m/Y H:i'),
            'userName' => $notification->getUser() ? $notification->getUser()->getUsername() : 'Utilisateur'
        ];
    }

    return new JsonResponse([
        'notifications' => $formattedNotifications,
        'unreadCount' => $unreadCount
    ]);
}

#[Route('/mark-notification-read/{id}', name: 'mark_notification_read')]
public function markNotificationAsRead(
    \App\Entity\Notification $notification, 
    EntityManagerInterface $entityManager
): JsonResponse {
    $notification->setIsRead(true);
    $entityManager->flush();
    
    return new JsonResponse(['success' => true]);
}
} 