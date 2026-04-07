<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_LIBRARIAN')]
#[Route('/librarian')]
class LibrarianController extends AbstractController
{
    #[Route('/dashboard', name: 'librarian_dashboard')]
    public function dashboard(
        LivreRepository $livreRepository,
        UserRepository $userRepository
    ): Response {
        $livres = $livreRepository->findAll();
        $users = $userRepository->findUsersWithReservations();

        return $this->render('librarian/index.html.twig', [
            'user' => $this->getUser(),
            'livres' => $livres,
            'users' => $users,
        ]);
    }

    #[Route('/reservations', name: 'librarian_reservations')]
    public function reservations(
        UserRepository $userRepository,
        ReservationRepository $reservationRepository
    ): Response {
        // Tous les users avec au moins une réservation
        $users = $userRepository->findUsersWithReservations();

        return $this->render('librarian/reservations.html.twig', [
            'user' => $this->getUser(),
            'users' => $users,
        ]);
    }

    #[Route('/reservations/user/{id}', name: 'librarian_user_reservations')]
    public function userReservations(
        int $id,
        UserRepository $userRepository,
        ReservationRepository $reservationRepository
    ): Response {
        $userCible = $userRepository->find($id);

        if (!$userCible) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $users = $userRepository->findUsersWithReservations(); // ajout
        $reservations = $reservationRepository->findBy(
            ['user' => $userCible],
            ['dateDebut' => 'DESC']
        );

        return $this->render('librarian/user_reservations.html.twig', [
            'user' => $this->getUser(),
            'users' => $users, // ajout
            'userCible' => $userCible,
            'reservations' => $reservations,
        ]);
    }
}