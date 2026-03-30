<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/film')]
class FilmSeatController extends AbstractController
{
    #[Route('/{id}/seats', name: 'api_film_seats', methods: ['GET'])]
    public function getSeats(Request $request, Film $film, EntityManagerInterface $entityManager): JsonResponse
    {
        $dateStr = $request->query->get('date');
        $salleId = $request->query->get('salle');
        
        $date = \DateTime::createFromFormat('Y-m-d', $dateStr);
        
        if (!$date) {
            return new JsonResponse([
                'error' => 'Invalid date format',
                'bookedSeats' => []
            ], 400);
        }

        // Set time to start of day to match all reservations on that date
        $date->setTime(0, 0, 0);

        // Get all booked seats for this film, date and cinema from all users
        $qb = $entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->select('r.selectedSeats')
            ->where('r.film = :film')
            ->andWhere('DATE(r.dateReservation) = DATE(:date)')
            ->andWhere('r.status != :cancelled_status')
            ->setParameter('film', $film)
            ->setParameter('date', $date)
            ->setParameter('cancelled_status', 'cancelled');

        // Add salle condition if provided
        if ($salleId) {
            $qb->andWhere('r.salle = :salle')
               ->setParameter('salle', $salleId);
        }

        $bookedSeats = $qb->getQuery()->getResult();

        // Flatten the array of booked seats
        $allBookedSeats = [];
        foreach ($bookedSeats as $seats) {
            if ($seats['selectedSeats']) {
                // Handle both JSON string and array formats
                $seatData = $seats['selectedSeats'];
                if (is_string($seatData)) {
                    $seatData = json_decode($seatData, true) ?? [];
                }
                if (is_array($seatData)) {
                    $allBookedSeats = array_merge($allBookedSeats, $seatData);
                }
            }
        }

        // Return unique booked seats
        return new JsonResponse([
            'bookedSeats' => array_values(array_unique($allBookedSeats))
        ]);
    }
} 