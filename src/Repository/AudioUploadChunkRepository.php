<?php

namespace App\Repository;

use App\Entity\AudioUploadChunk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AudioUploadChunk|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudioUploadChunk|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudioUploadChunk[]    findAll()
 * @method AudioUploadChunk[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudioUploadChunkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AudioUploadChunk::class);
    }

//    /**
//     * @return AudioUploadChunk[] Returns an array of AudioUploadChunk objects
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
    public function findOneBySomeField($value): ?AudioUploadChunk
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
