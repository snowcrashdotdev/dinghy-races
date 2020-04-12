<?php

namespace App\Repository;

use App\Entity\TournamentScore;
use App\Entity\Tournament;
use App\Entity\Game;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TournamentScore|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentScore|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentScore[]    findAll()
 * @method TournamentScore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentScoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentScore::class);
    }

    public function findTournamentResults(Tournament $tournament, ?Team $team=null, ?User $user=null, ?bool $groupByTeam=false)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.team', 't')
            ->join('s.tournament', 'n')
            ->join('s.user', 'u')
            ->addSelect('u.username as name')
            ->addSelect('SUM(s.ranked_points) as ranked_points')
            ->addSelect('SUM(s.team_points) as team_points')
            ->addSelect('AVG(s.rank) as avg_rank')
            ->addSelect('100 * SUM(case when s.auto_assigned = 1 then 0 else 1 end) / SIZE(n.games) as completion')
            ->groupBy('u.username')
            ->andWhere('s.tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->orderBy('ranked_points', 'DESC')
        ;

        if ($user) {
            $q->andWhere('s.user = :user')
                ->setParameter('user', $user)
            ;

            return $q->getQuery()->getOneOrNullResult();
        }

        if ($team) {
            $q->andWhere('s.team = :team')
                ->setParameter('team', $team)
            ;
        }

        return $q->getQuery()->getArrayResult();
    }

    public function findIndividualScores($tournament, $limit=null)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->leftJoin('s.team', 't')
            ->select('u.id as id', 'u.username as name', 't.name as team', 'SUM(s.ranked_points) as points')
            ->groupBy('s.user', 's.team')
            ->andWhere('s.tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->orderBy('points', 'DESC')
        ;

        if ( $limit ) {
            $q->setMaxResults($limit);
        }

        if ($limit === 1) {
            return $q->getQuery()->getOneOrNullResult();
        }

        return $q->getQuery()->getArrayResult();
    }

    public function findTeamScores(Tournament $tournament, Game $game=null, $limit=null)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.team', 't')
            ->select('t.id as id', 't.name as name', 'SUM(s.team_points) as points')
            ->groupBy('s.team')
            ->andWhere('s.tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->orderBy('points', 'DESC');

        if ($game !== null) {
            $q->andWhere('s.game = :game')
                ->setParameter('game', $game);
        }

        if ($limit) {
            $q->setMaxResults($limit);
        }

        if ($limit === 1) {
            return $q->getQuery()->getOneOrNullResult();
        }

        return $q->getQuery()->getArrayResult();
    }

    public function findTeamScoresPerGame(Team $team)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.game', 'g')
            ->select('g.id as game', 'g.description as description', 'SUM(s.team_points) as points')
            ->groupBy('s.game')
            ->andWhere('s.team = :team')
            ->setParameter('team', $team)
            ->andWhere('s.team_points > 0')
            ->orderBy('points', 'DESC');

        return $q->getQuery()->getResult();
    }

    public function findByTournamentPublic(Tournament $tournament)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->join('s.team', 't')
            ->select('s.points', 'u.username', 't.name as team')
            ->andWhere('s.tournament = :tournament')
            ->setParameter('tournament', $tournament)
        ;

        return $q->getQuery()->getArrayResult();
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

    // /**
    //  * @return TournamentScore[] Returns an array of TournamentScore objects
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
    public function findOneBySomeField($value): ?TournamentScore
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
