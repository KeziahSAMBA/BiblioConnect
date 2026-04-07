<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/user/reservation')]
final class ReservationController extends AbstractController
{
    public function __construct(
        private ReservationService $reservationService
    ) {}

    #[Route('/new/{livre?}', name: 'user_reservation_new', methods: ['GET', 'POST'], requirements: ['livre' => '\\d+'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ?Livre $livre = null): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$this->reservationService->canUserReserve($user->getId())) {
            $this->addFlash('error', 'Vous ne pouvez pas avoir plus de 2 réservations actives simultanément.');
            return $this->redirectToRoute('user_dashboard');
        }

        $reservation = new Reservation();
        $reservation->setUser($user);

        if ($livre) {
            $reservation->setLivre($livre);
        }

        $form = $this->createForm(ReservationType::class, $reservation, [
            'livre_auto' => (bool) $livre,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $livre = $reservation->getLivre();
            $dateDebut = $reservation->getDateDebut();

            if (!$dateDebut) {
                $this->addFlash('error', 'Veuillez choisir une date de début de réservation valide.');
                return $this->redirectToRoute('user_dashboard');
            }

            $dateDebut->setTime(0, 0, 0); // force minuit APRÈS la vérification null
            $today = new \DateTime('today');
            $dateFin = (clone $dateDebut)->modify('+7 days');

            if ($dateDebut < $today) {
                $this->addFlash('error', 'La date de début doit être aujourd\'hui ou ultérieure.');
                return $this->redirectToRoute('user_dashboard');
            }

            if ($livre->getStock() <= 0) {
                $this->addFlash('error', 'Ce livre n\'est plus disponible.');
                return $this->redirectToRoute('user_dashboard');
            }

            if (!$this->reservationService->isLivreAvailableForDates($livre, $dateDebut, $dateFin)) {
                $this->addFlash('error', 'Ce livre n\'est pas disponible sur ces dates.');
                return $this->redirectToRoute('user_dashboard');
            }

            if ($this->reservationService->hasOverlappingReservationForUser($user->getId(), $livre, $dateDebut, $dateFin)) {
                $this->addFlash('error', 'Vous avez déjà une réservation pour ce livre sur ces dates.');
                return $this->redirectToRoute('user_dashboard');
            }

            $reservation->setDateFin($dateFin);
            $reservation->setStatut($this->reservationService->determineReservationStatus($dateDebut, $dateFin));

            // Stock -1 quand réservation active
            if ($reservation->getStatut() === 'en_attente'||$reservation->getStatut() === 'actif') {
                $livre->setStock($livre->getStock() - 1);
            }

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Réservation enregistrée pour "%s" du %s au %s. Statut : %s.',
                $livre->getTitre(),
                $dateDebut->format('d/m/Y'),
                $dateFin->format('d/m/Y'),
                ucfirst($reservation->getStatut())
            ));

            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($reservation->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/terminer', name: 'user_reservation_terminer', methods: ['POST'])]
    public function terminer(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($reservation->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($reservation->getStatut() === 'actif') {
            $reservation->setStatut('terminé');

            // Stock +1 quand réservation terminée
            $livre = $reservation->getLivre();
            $livre->setStock($livre->getStock() + 1);

            $entityManager->flush();
            $this->addFlash('success', 'Réservation terminée avec succès.');
        } else {
            $this->addFlash('error', 'Seules les réservations actives peuvent être terminées.');
        }

        return $this->redirectToRoute('user_profil');
    }

    #[Route('/{id}/supprimer', name: 'user_reservation_supprimer', methods: ['POST'])]
    public function supprimer(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($reservation->getStatut() === 'en_attente') {
            $livre = $reservation->getLivre();
            $livre->setStock($livre->getStock() + 1);
            
            $entityManager->remove($reservation);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Seules les réservations en attente peuvent être supprimées.');
        }

        return $this->redirectToRoute('user_profil');
    }
}