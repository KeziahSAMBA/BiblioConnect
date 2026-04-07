<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CommentaireRepository;
use App\Repository\LivreRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        LivreRepository $livreRepository,
        ReservationRepository $reservationRepository,
        UserRepository $userRepository,
        CommentaireRepository $commentaireRepository
    ): Response {
        return $this->render('admin/index.html.twig', [
            'user' => $this->getUser(),
            'livres' => $livreRepository->findAll(),
            'reservationsEnAttente' => $reservationRepository->findBy(['statut' => 'en_attente']),
            'reservationsActives' => $reservationRepository->findBy(['statut' => 'actif']),
            'users' => $userRepository->findAll(),
            'commentaires' => $commentaireRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    // Gestion des rôles
    #[Route('/user/{id}/role', name: 'admin_user_role', methods: ['POST'])]
    public function changeRole(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $userCible = $userRepository->find($id);

        if (!$userCible) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $role = $request->request->get('role');
        $rolesValides = ['ROLE_USER', 'ROLE_LIBRARIAN', 'ROLE_ADMIN'];

        if (in_array($role, $rolesValides)) {
            $userCible->setRoles([$role]);
            $entityManager->flush();
            $this->addFlash('success', 'Rôle mis à jour avec succès.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    // Suppression d'un utilisateur
    #[Route('/user/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $userCible = $userRepository->find($id);

        if ($userCible) {
            $entityManager->remove($userCible);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    // Modération commentaires
    #[Route('/commentaire/{id}/delete', name: 'admin_commentaire_delete', methods: ['POST'])]
    public function deleteCommentaire(
        int $id,
        CommentaireRepository $commentaireRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $commentaire = $commentaireRepository->find($id);

        if ($commentaire) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    // Historique réservations d'un user
    #[Route('/user/{id}/reservations', name: 'admin_user_reservations')]
    public function userReservations(
        int $id,
        UserRepository $userRepository,
        ReservationRepository $reservationRepository
    ): Response {
        $userCible = $userRepository->find($id);

        if (!$userCible) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $reservations = $reservationRepository->findBy(
            ['user' => $userCible],
            ['dateDebut' => 'DESC']
        );

        return $this->render('admin/user_reservations.html.twig', [
            'user' => $this->getUser(),
            'userCible' => $userCible,
            'reservations' => $reservations,
        ]);
    }
}