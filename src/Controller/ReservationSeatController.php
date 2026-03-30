<?php

namespace App\Controller;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reservation')]
class ReservationSeatController extends AbstractController
{
    #[Route('/{id}/seats', name: 'api_reservation_seats', methods: ['GET'])]
    public function getSeats(Reservation $reservation, EntityManagerInterface $entityManager): JsonResponse
    {
        // Get all booked seats for this reservation
        $bookedSeats = $entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->select('r.selectedSeats')
            ->where('r.id != :id')
            ->andWhere('r.dateReservation = :date')
            ->andWhere('r.film = :film')
            ->setParameters([
                'id' => $reservation->getId(),
                'date' => $reservation->getDateReservation(),
                'film' => $reservation->getFilm()
            ])
            ->getQuery()
            ->getResult();

        // Flatten the array of booked seats
        $allBookedSeats = [];
        foreach ($bookedSeats as $seats) {
            if ($seats['selectedSeats']) {
                $allBookedSeats = array_merge($allBookedSeats, json_decode($seats['selectedSeats']));
            }
        }

        return new JsonResponse([
            'bookedSeats' => array_unique($allBookedSeats),
            'currentSeats' => $reservation->getSelectedSeats() ? json_decode($reservation->getSelectedSeats()) : []
        ]);
    }

    #[Route('/{id}/seats', name: 'api_reservation_seats_save', methods: ['POST'])]
    public function saveSeats(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $seats = $data['seats'] ?? [];

        if (count($seats) > $reservation->getNombrePlaces()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Too many seats selected'
            ], 400);
        }

        // Check if any of these seats are already booked
        $bookedSeats = $entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->select('r.selectedSeats')
            ->where('r.id != :id')
            ->andWhere('r.dateReservation = :date')
            ->andWhere('r.film = :film')
            ->setParameters([
                'id' => $reservation->getId(),
                'date' => $reservation->getDateReservation(),
                'film' => $reservation->getFilm()
            ])
            ->getQuery()
            ->getResult();

        $allBookedSeats = [];
        foreach ($bookedSeats as $bookedSeat) {
            if ($bookedSeat['selectedSeats']) {
                $allBookedSeats = array_merge($allBookedSeats, json_decode($bookedSeat['selectedSeats']));
            }
        }

        // Check for conflicts
        $conflicts = array_intersect($seats, $allBookedSeats);
        if (!empty($conflicts)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Some seats are already booked'
            ], 400);
        }

        // Save the seats
        $reservation->setSelectedSeats(json_encode($seats));
        $entityManager->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }
} 