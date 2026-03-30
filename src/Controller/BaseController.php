<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class BaseController extends AbstractController
{
    protected SessionInterface $session;
    protected UserRepository $userRepository;
    protected ReservationRepository $reservationRepository;

    public function __construct(
        SessionInterface $session,
        UserRepository $userRepository,
        ReservationRepository $reservationRepository
    ) {
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->reservationRepository = $reservationRepository;
    }

    protected function getRecentReservations(): array
    {
        $userId = $this->session->get('id');
        if (!$userId) {
            // Debug message for missing user ID
            error_log("No user ID found in session");
            return [];
        }
        
        $user = $this->userRepository->find($userId);
        if (!$user) {
            // Debug message for user not found
            error_log("User not found for ID: " . $userId);
            return [];
        }

        $reservations = $this->reservationRepository->findRecentReservations($user);
        // Debug message for reservations found
        error_log("Found " . count($reservations) . " reservations for user ID: " . $userId);
        error_log("User email: " . $user->getEmail());
        
        return $reservations;
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        // Always include recent_reservations in the template parameters
        if (!isset($parameters['recent_reservations'])) {
            $parameters['recent_reservations'] = $this->getRecentReservations();
        }
        
        // Add user information to the template
        if (!isset($parameters['user'])) {
            $userId = $this->session->get('id');
            if ($userId) {
                $user = $this->userRepository->find($userId);
                if ($user) {
                    $parameters['user'] = $user;
                }
            }
        }
        
        return parent::render($view, $parameters, $response);
    }
} 