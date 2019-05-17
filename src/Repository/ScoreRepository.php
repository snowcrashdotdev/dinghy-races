<?php

namespace App\Repository;

use App\Entity\Score;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Score|null find($id, $lockMode = null, $lockVersion = null)
 * @method Score|null findOneBy(array $criteria, array $orderBy = null)
 * @method Score[]    findAll()
 * @method Score[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScoreRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Score::class);
    }

    public function findByGameAndTournament($tournament, $game, $limit=null)
    {
        $q = $this->createQueryBuilder('s')
            ->andWhere('s.tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->andWhere('s.game = :game')
            ->setParameter('game', $game)
            ->orderBy('s.points', 'DESC');
        if ($limit !== null) {
            $q->setMaxResults($limit);
        }
        
        return $q->getQuery()->getResult();
    }

    public function findIndividualScores($tournament, $limit=null)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.user', 'user')
            ->select('user.username as username', 'SUM(s.points) as points')
            ->groupBy('s.user')
            ->andWhere('s.tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->orderBy('points', 'DESC')
        ;

        if ( $limit ) {
            $q->setMaxResults($limit);
        }

        return $q->getQuery()->getArrayResult();
    }

    public function findCountGreaterThanPoints(Score $score)
    {
        $q = $this->createQueryBuilder('s')
            ->select('s.id')
            ->andWhere('s.id != :id')
            ->setParameter('id', $score->getId())
            ->andWhere('s.tournament = :tournament')
            ->setParameter('tournament', $score->getTournament())
            ->andWhere('s.game = :game')
            ->setParameter('game', $score->getGame())
            ->andWhere('s.points >= :points')
            ->setParameter('points', $score->getPoints())
            ->getQuery();

        return count($q->execute());
    }

    // /**
    //  * @return Score[] Returns an array of Score objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Score
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
