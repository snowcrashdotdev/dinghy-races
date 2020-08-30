<?php

namespace App\Repository;

use App\Entity\PersonalBest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PersonalBest|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonalBest|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonalBest[]    findAll()
 * @method PersonalBest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonalBestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonalBest::class);
    }

    // /**
    //  * @return PersonalBest[] Returns an array of PersonalBest objects
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
    public function findOneBySomeField($value): ?PersonalBest
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
