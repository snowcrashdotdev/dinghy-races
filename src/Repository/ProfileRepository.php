<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\Tournament;
use App\Entity\TournamentUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Profile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profile|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profile[]    findAll()
 * @method Profile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function findTournamentTwitchLinks(Tournament $tournament)
    {
        $q = $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->join('u.appearances', 't')
            ->andWhere('t.tournament = :tournament')
            ->andWhere('p.social LIKE :twitch')
            ->setParameter('tournament', $tournament)
            ->setParameter('twitch', '%twitch.tv%')
            ->select('p.social')
        ;

        return $q->getQuery()->getArrayResult();
    }

    // /**
    //  * @return Profile[] Returns an array of Profile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Profile
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
