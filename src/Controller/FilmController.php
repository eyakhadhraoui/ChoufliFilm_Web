<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Reservation;
use App\Form\FilmType;
use App\Form\FilmeditType;

use App\Form\ReservationType;
use App\Repository\FilmRepository;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\SalleRepository;
use App\Repository\AssociationRepository;
use App\Controller\BaseController;

final class FilmController extends BaseController
{
    private EntityManagerInterface $entityManager;
    public function __construct(
        EntityManagerInterface $entityManager,
        protected RequestStack $requestStack,
        protected UserRepository $userRepository,
        protected ReservationRepository $reservationRepository
    ) {
        $this->entityManager = $entityManager;
        parent::__construct($requestStack->getSession(), $userRepository, $reservationRepository);
    }

    #[Route('/film', name: 'app_film')]
    public function index(): Response
    {
        return $this->render('film/index.html.twig', [
            'controller_name' => 'FilmController',
        ]);
    }
    
    #[Route('/filmback', name: 'filmback')]
    public function filmback(Request $request): Response
    {
        $film = new Film();
        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // No image handling code is needed here if there's no image field.
            $this->entityManager->persist($film);
            $this->entityManager->flush();

            return $this->redirectToRoute('listfilm');
        }

        return $this->render('film/filmback.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/front/showallfilm', name: 'showallfilm')]
    public function showAllFilm(FilmRepository $filmRepository): Response
    {
        $films = $filmRepository->findAll();
        return $this->render('film/showfront.html.twig', [
            'films' => $films,
        ]);
    }
    #[Route('/film/new', name: 'film_new')]
    public function new(Request $request): Response
    {
        $film = new Film();
        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image_film')->getData();
            if ($imageFile !== null) {
                $filename = uniqid() . '-' . $imageFile->getClientOriginalName();
                $imageFile->move(
                    $this->getParameter('upload_directory'),
                    $filename
                );
                $film->setImageFilm($filename);
            } else {
                // If an image is required, you might want to add a flash message or error.
                return $this->render('film/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            
            $this->entityManager->persist($film);
            $this->entityManager->flush();

            return $this->redirectToRoute('listfilm');
        }
    
        return $this->render('film/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/film/{id}/reserve', name: 'app_film_reserve', methods: ['GET', 'POST'])]
    public function reserve(
        Request $request, 
        Film $film, 
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack, 
        UserRepository $userRepository,
        SalleRepository $salleRepository,
        AssociationRepository $associationRepository
    ): Response
    {
        // Get the current user from session
        $userId = $requestStack->getSession()->get('id');
        if (!$userId) {
            return $this->redirectToRoute('app_login');
        }
        
        $user = $userRepository->find($userId);
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $reservation = new Reservation();
        $reservation->setDateReservation(new \DateTime());
        $reservation->setFilm($film);
        $reservation->setTitre($film->getTitre());
        $reservation->setUser($user);

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typePlace = $form->get('type_Place')->getData();
            $nombrePlaces = $form->get('nombre_places')->getData();
            $salle = $form->get('salle')->getData();
            $association = $form->get('association')->getData();
            
            // Validate selected seats
            $selectedSeats = $request->request->get('selectedSeats');
            if (!$selectedSeats) {
                $this->addFlash('error', 'Please select your seats');
                return $this->redirectToRoute('app_film_reserve', ['id' => $film->getId()]);
            }

            // Validate number of selected seats matches nombre_places
            $selectedSeatsArray = json_decode($selectedSeats);
            if (!is_array($selectedSeatsArray) || count($selectedSeatsArray) !== $nombrePlaces) {
                $this->addFlash('error', 'Number of selected seats does not match the requested number of seats');
                return $this->redirectToRoute('app_film_reserve', ['id' => $film->getId()]);
            }
            
            $reservation->setTypePlace($typePlace);
            $reservation->setNombrePlaces($nombrePlaces);
            $reservation->setSalle($salle);
            $reservation->setAssociation($association);
            $reservation->setStatus('pending');
            $reservation->setSelectedSeats($selectedSeats);
            
            try {
                $entityManager->persist($reservation);
                $entityManager->flush();

                // Get the clicked button
                $clickedButton = $request->request->all()['reservation'] ?? [];
                $clickedButton = array_key_exists('save', $clickedButton) ? 'save' : 'pay';

                if ($clickedButton === 'save') {
                    $this->addFlash('success', sprintf(
                        'Your reservation of %d seat(s) for %s has been added to your cart!',
                        $reservation->getNombrePlaces(),
                        $film->getTitre()
                    ));
                    return $this->redirectToRoute('app_cart');
                } else {
                    return $this->redirectToRoute('app_payment', ['id' => $reservation->getId()]);
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while saving your reservation. Please try again.');
                return $this->redirectToRoute('app_film_reserve', ['id' => $film->getId()]);
            }
        }

        return $this->render('film/reserve.html.twig', [
            'film' => $film,
            'form' => $form->createView(),
            'movie_details' => [
                'id' => $film->getId(),
                'titre' => $film->getTitre(),
                'description' => $film->getDescription(),
                'duree' => $film->getDuree(),
                'directeur' => $film->getDirecteur(),
                'note' => $film->getNote(),
                'image' => $film->getImageFilm(),
                'date_fin' => $film->getDateFin()->format('Y-m-d')
            ]
        ]);
    }

    #[Route('/user/front/cart', name: 'app_cart')]
    public function cart(): Response
    {
        
        $userId = $this->requestStack->getSession()->get('id');
        if (!$userId) {
            $this->addFlash('error', 'Please log in to view your cart.');
            return $this->redirectToRoute('app_login');
        }
        
        $user = $this->userRepository->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Please log in to view your cart.');
            return $this->redirectToRoute('app_login');
        }

        // Get all pending reservations for the current user
        $reservations = $this->reservationRepository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('film/cart.html.twig', [
            'reservations' => $reservations,
            'user' => $user
        ]);
    }

    #[Route('/payment/{id}', name: 'app_payment')]
    public function payment(Reservation $reservation, RequestStack $requestStack, UserRepository $userRepository): Response
    {
        // Get the current user from session
        $userId = $requestStack->getSession()->get('id');
        if (!$userId) {
            $this->addFlash('error', 'Please log in to proceed with payment.');
            return $this->redirectToRoute('app_login');
        }
        
        $user = $userRepository->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Please log in to proceed with payment.');
            return $this->redirectToRoute('app_login');
        }

        // Check if the reservation belongs to the current user
        if ($reservation->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You cannot access this reservation.');
        }

        // Check if the reservation is already paid
        if ($reservation->getStatus() === 'paid') {
            $this->addFlash('warning', 'This reservation has already been paid.');
            return $this->redirectToRoute('showallfilm');
        }

        // Calculate total amount
        $pricePerSeat = $reservation->getTypePlace() === 'VIP' ? 15 : 10;
        $totalAmount = $reservation->getNombrePlaces() * $pricePerSeat;

        // Get cinema and association names with null checks
        $cinema = $reservation->getSalle() ? $reservation->getSalle()->getNomSalle() : 'N/A';
        $association = $reservation->getAssociation() ? $reservation->getAssociation()->getNom() : 'N/A';

        return $this->render('film/payment.html.twig', [
            'reservation' => $reservation,
            'user' => $user,
            'total_amount' => $totalAmount,
            'cinema' => $cinema,
            'association' => $association
        ]);
    }

    #[Route('/payment/{id}/process', name: 'app_payment_process', methods: ['POST'])]
    public function processPayment(
        Request $request, 
        Reservation $reservation, 
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack, 
        UserRepository $userRepository
    ): Response
    {
        // Get the current user from session
        $userId = $requestStack->getSession()->get('id');
        if (!$userId) {
            $this->addFlash('error', 'Please log in to proceed with payment.');
            return $this->redirectToRoute('app_login');
        }
        
        $user = $userRepository->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Please log in to proceed with payment.');
            return $this->redirectToRoute('app_login');
        }

        // Check if the reservation belongs to the current user
        if ($reservation->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You cannot process payment for this reservation.');
        }

        // Check if the reservation is already paid
        if ($reservation->getStatus() === 'paid') {
            $this->addFlash('warning', 'This reservation has already been paid.');
            return $this->redirectToRoute('showallfilm');
        }

        // Validate payment details (in a real app, you would process payment here)
        $cardNumber = $request->request->get('cardNumber');
        $expiry = $request->request->get('expiry');
        $cvv = $request->request->get('cvv');
        $cardName = $request->request->get('cardName');

        if (!$cardNumber || !$expiry || !$cvv || !$cardName) {
            $this->addFlash('error', 'Please fill in all payment details.');
            return $this->redirectToRoute('app_payment', ['id' => $reservation->getId()]);
        }

        // Process payment (mock success for demo)
        $reservation->setStatus('paid');
        $entityManager->flush();

        $this->addFlash('success', 'Payment processed successfully! Your tickets have been confirmed.');
        return $this->redirectToRoute('showallfilm');
    }

  

    #[Route('/film/{id}', name: 'app_film_show', methods: ['GET'])]
    public function show(Film $film): Response
    {
        return $this->render('film/show.html.twig', [
            'film' => $film,
        ]);
    }

    #[Route('/film/edit/{id}', name: 'app_film_edit')]
    public function edit(Request $request, Film $film): Response
    {
        $form = $this->createForm(FilmeditType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image_film')->getData();
            if ($imageFile !== null) {
                $filename = uniqid() . '-' . $imageFile->getClientOriginalName();
                $imageFile->move(
                    $this->getParameter('upload_directory'),
                    $filename
                );
                $film->setImageFilm($filename);
            }

            $this->entityManager->flush();
            return $this->redirectToRoute('listfilm');
        }

        return $this->render('film/edit.html.twig', [
            'form' => $form->createView(),
            'film' => $film,
        ]);
    }

    #[Route('/film/delete/{id}', name: 'app_film_delete')]
    public function delete(FilmRepository $repo,EntityManagerInterface $em,int $id): Response
    {
        $film =$repo->find($id);
 if($film){
    $em->remove($film);
    $em->flush();

        return $this->redirectToRoute('listfilm');
    }
}

    #[Route('/user/front/{id}/deatilfront', name: 'detailfilm1')]
    public function detailfilm(FilmRepository $repo, int $id): Response
    {
        $film = $repo->find($id);
        
        return $this->render('salle/deatilfront.html.twig', [
           'film' => $film,
        ]);
    }
    #[Route('/listfilm', name: 'listfilm')]
    public function list(): Response
    {
        $films = $this->entityManager->getRepository(Film::class)->findAll();

        return $this->render('film/listfilm.html.twig', [
            'films' => $films,
        ]);
    }
} 