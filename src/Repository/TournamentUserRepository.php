<?php

namespace App\Repository;

use App\Entity\TournamentUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TournamentUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentUser[]    findAll()
 * @method TournamentUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentUser::class);
    }

    public function findTournamentRivals(User $user, array $tournaments)
    {
        $r = $this->createQueryBuilder('u')
            ->select('AVG(u.avg_rank)')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
        ;

        $rank = $r->getQuery()->getSingleScalarResult();

        $q = $this->createQueryBuilder('u')
            ->addSelect('ABS(AVG(u.avg_rank) - :rank) AS HIDDEN diff')
            ->andWhere('u.tournament IN (:tournaments)')
            ->andWhere('u.user != :user')
            ->andWhere('u.ranked_points > 0')
            ->groupBy('u.user')
            ->orderBy('diff', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('rank', $rank)
            ->setParameter('tournaments', $tournaments)
            ->setMaxResults(5)
        ;

        return $q->getQuery()->getResult();
    }

    // /**
    //  * @return TournamentUser[] Returns an array of TournamentUser objects
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
    public function findOneBySomeField($value): ?TournamentUser
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
