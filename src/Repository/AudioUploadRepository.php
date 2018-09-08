<?php

namespace App\Repository;

use App\Entity\AudioUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AudioUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudioUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudioUpload[]    findAll()
 * @method AudioUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudioUploadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AudioUpload::class);
    }

//    /**
//     * @return AudioUpload[] Returns an array of AudioUpload objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AudioUpload
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
