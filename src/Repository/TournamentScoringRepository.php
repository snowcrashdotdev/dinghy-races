<?php

namespace App\Repository;

use App\Entity\TournamentScoring;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TournamentScoring|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentScoring|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentScoring[]    findAll()
 * @method TournamentScoring[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentScoringRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentScoring::class);
    }

    // /**
    //  * @return TournamentScoring[] Returns an array of TournamentScoring objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TournamentScoring
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
