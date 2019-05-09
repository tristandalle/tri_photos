<?php

namespace App\Repository;

use App\Entity\PasswordModify;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PasswordModify|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordModify|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordModify[]    findAll()
 * @method PasswordModify[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordModifyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PasswordModify::class);
    }

    // /**
    //  * @return PasswordModify[] Returns an array of PasswordModify objects
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
    public function findOneBySomeField($value): ?PasswordModify
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
