<?php

namespace App\Repository;

use App\Entity\InboxMail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InboxMail|null find($id, $lockMode = null, $lockVersion = null)
 * @method InboxMail|null findOneBy(array $criteria, array $orderBy = null)
 * @method InboxMail[]    findAll()
 * @method InboxMail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InboxMailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboxMail::class);
    }

    // /**
    //  * @return InboxMail[] Returns an array of InboxMail objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InboxMail
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
