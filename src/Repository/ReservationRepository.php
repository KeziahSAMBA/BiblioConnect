<?php

namespace App\Repository;

use App\Entity\Livre;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function countActiveReservationsForUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :userId')
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('userId', $userId)
            ->setParameter('statuts', ['actif', 'en_attente'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOverlappingReservations(
        Livre $livre,
        \DateTime $start,
        \DateTime $end
    ): int {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.livre = :livre')
            ->andWhere('r.dateDebut <= :end')
            ->andWhere('r.dateFin >= :start')
            ->andWhere('r.statut IN (:statats)')
            ->setParameter('livre', $livre)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('statats', ['active', 'pending'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function hasOverlappingReservationForUser(
        int $userId,
        Livre $livre,
        \DateTime $start,
        \DateTime $end
    ): bool {
        $count = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :user')
            ->andWhere('r.livre = :livre')
            ->andWhere('r.dateDebut <= :end')
            ->andWhere('r.dateFin >= :start')
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('user', $userId)
            ->setParameter('livre', $livre)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('statuts', ['actif', 'en_attente'])
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function countReservedCopies(int $livreId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.livre = :livreId')
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('livreId', $livreId)
            ->setParameter('statuts', ['actif', 'en_attente'])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
