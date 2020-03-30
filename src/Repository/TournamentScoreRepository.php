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



    public function findSubmittedTournamentScores(Tournament $tournament=null, Game $game=null, Team $team=null, Integer $limit=null)
    {
        $q = $this->createQueryBuilder('s')
            ->andWhere('s.auto_assigned != 1')
            ->orderBy('s.points', 'DESC')
        ;

        if ($tournament !== null) {
            $q->andWhere('s.tournament = :tournament')
                ->setParameter('tournament', $tournament)
            ;
        }

        if ($game !== null) {
            $q->andWhere('s.game = :game')
                ->setParameter('game', $game)
            ;
        }

        if ($team !== null) {
            $q->andWhere('s.team = :team')
                ->setParameter('team', $team)
            ;
        }

        return $q->getQuery()->getResult();
    }

    public function findTotalTeamPoints(Team $team)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.team = :team')
            ->setParameter('team', $team)
            ->select('SUM(s.team_points) as total')
            ->getQuery()->getSingleScalarResult();
    }

    public function findRecentScores(Tournament $tournament, $limit=null)
    {
        $q = $this->createQueryBuilder('s')
            ->andWhere('s.tournament = :tournament')
            ->andWhere('s.points != 0')
            ->orderBy('s.updated_at', 'DESC')
            ->setParameter('tournament', $tournament);
        
        if ($limit !== null) {
            $q->setMaxResults($limit);
        }

        return $q->getQuery()->getResult();
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

        return $q->getQuery()->getArrayResult();
    }

    public function findTeamScores(Tournament $tournament, Game $game=null, $limit=null)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.team', 't')
            ->select('t.id as id', 't.name as name', 'SUM(s.ranked_points) as points')
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

        return $q->getQuery()->getArrayResult();
    }

    public function findTeamLeaderboard(Team $team)
    {
        $q = $this->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->select('u.id as user', 'u.username as username', 'SUM(s.ranked_points) as points', 'SUM(CASE WHEN s.points > 0 THEN 1 ELSE 0 END) as completed')
            ->groupBy('s.user')
            ->andWhere('s.team = :team')
            ->setParameter('team', $team)
            ->orderBy('points', 'DESC');

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

    public function findUserScores(User $user, $limit=null)
    {
        $q = $this->createQueryBuilder('s')
            ->select('s')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->andWhere('s.points > 0')
            ->orderBy('s.updated_at', 'DESC')
            ->setMaxResults($limit);

        return $q->getQuery()->getResult();
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