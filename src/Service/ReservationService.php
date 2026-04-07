<?php

namespace App\Service;

use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReservationService
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Traite les réservations expirées et met à jour leur statut
     */
   public function processExpiredReservations(): int
{
    $now = new \DateTime();
    $expiredReservations = $this->reservationRepository->createQueryBuilder('r')
        ->where('r.dateFin < :now')
        ->andWhere('r.statut IN (:statuts)')
        ->setParameter('now', $now)
        ->setParameter('statuts', ['actif', 'en_attente'])
        ->getQuery()
        ->getResult();

    $count = 0;
    foreach ($expiredReservations as $reservation) {
        $reservation->setStatut('terminé');
        // Stock +1 quand réservation expirée automatiquement
        $reservation->getLivre()->setStock($reservation->getLivre()->getStock() + 1);
        $count++;
    }

    $this->entityManager->flush();
    return $count;
}

    public function determineReservationStatus(\DateTime $dateDebut, \DateTime $dateFin): string
    {
        $today = new \DateTime('today', new \DateTimeZone('Europe/Paris'));
        $dateDebutParis = (clone $dateDebut)->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateDebutParis->setTime(0, 0, 0);

        if ($dateDebutParis <= $today) {
            return 'actif';
        }

        return 'en_attente';
    }

    /**
     * Vérifie si un utilisateur peut faire une nouvelle réservation
     */
    public function canUserReserve(int $userId): bool
    {
        return $this->reservationRepository->countActiveReservationsForUser($userId) < 4;
    }

    public function isLivreAvailableForDates(\App\Entity\Livre $livre, \DateTime $start, \DateTime $end): bool
    {
        $overlappingReservations = $this->reservationRepository->countOverlappingReservations($livre, $start, $end);

        return $overlappingReservations < $livre->getStock();
    }

    public function hasOverlappingReservationForUser(int $userId, \App\Entity\Livre $livre, \DateTime $start, \DateTime $end): bool
    {
        return $this->reservationRepository->hasOverlappingReservationForUser($userId, $livre, $start, $end);
    }

    /**
     * Obtient le nombre de réservations actives d'un utilisateur
     */
    public function getActiveReservationsCount(int $userId): int
    {
        return $this->reservationRepository->countActiveReservationsForUser($userId);
    }
}