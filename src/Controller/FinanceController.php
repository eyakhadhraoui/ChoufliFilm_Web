<?php

namespace App\Controller;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FinanceController extends AbstractController
{
    #[Route('/finance/back/showall', name: 'finance_back_showall')]
    public function showAllFinance(EntityManagerInterface $entityManager): Response
    {
        // Get all reservations with user information
        $reservations = $entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.film', 'f')
            ->select('r', 'u', 'f')
            ->getQuery()
            ->getResult();

        return $this->render('finance/showall.html.twig', [
            'reservations' => $reservations
        ]);
    }


    
} 