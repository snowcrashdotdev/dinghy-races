<?php

namespace App\Repository;

use App\Entity\Tournament;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Tournament|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tournament|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tournament[]    findAll()
 * @method Tournament[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    public function findAll()
    {
        return $this->findBy([], ['start_date' => 'DESC']);
    }

    public function findForUser(User $user, $timing='UPCOMING')
    {
        $q = $this->createQueryBuilder('t')
            ->join('t.users', 'tu')
            ->andWhere('tu.user = :user')
            ->setParameter('user', $user)
            ->andWhere('t.end_date > CURRENT_DATE()')
        ;

        if ($timing === 'IN_PROGRESS') {
            $q->andWhere('t.start_date <= CURRENT_DATE()');
        } else {
            $q->andWhere('t.start_date > CURRENT_DATE()');
        }

        return $q->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Tournament
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
