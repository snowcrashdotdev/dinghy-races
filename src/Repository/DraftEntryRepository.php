<?php

namespace App\Repository;

use App\Entity\DraftEntry;
use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DraftEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method DraftEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method DraftEntry[]    findAll()
 * @method DraftEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DraftEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DraftEntry::class);
    }

    public function findEligiblePlayers(Tournament $tournament)
    {
        return $this->createQueryBuilder('e')
            ->join('e.user', 'p')
            ->join('p.user', 'u')
            ->join('e.draft', 'd')
            ->select('u.username as username')
            ->andWhere('e.eligible = :eligible')
            ->andWhere('d.tournament = :tournament')
            ->setParameter('eligible', true)
            ->setParameter('tournament', $tournament)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return DraftEntry[] Returns an array of DraftEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DraftEntry
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
