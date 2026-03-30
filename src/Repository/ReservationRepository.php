<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\User;
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

    public function findPendingReservationsByUser(User $user)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentReservations(User $user, int $limit = 3): array
    {
        error_log("Starting findRecentReservations for user: " . $user->getEmail());
        
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', ['pending', 'paid'])
            ->orderBy('r.dateReservation', 'DESC')
            ->setMaxResults($limit);

        $query = $qb->getQuery();
        $sql = $query->getSQL();
        error_log("Generated SQL: " . $sql);
        error_log("Parameters: user_id=" . $user->getId() . ", statuses=['pending','paid']");

        $result = $query->getResult();
        error_log("Query returned " . count($result) . " results");
        foreach ($result as $index => $reservation) {
            error_log("Reservation " . ($index + 1) . ": " .
                     "Title=" . $reservation->getTitre() . ", " .
                     "Status=" . $reservation->getStatus() . ", " .
                     "Date=" . $reservation->getDateReservation()->format('Y-m-d'));
        }
        
        return $result;
    }
}
