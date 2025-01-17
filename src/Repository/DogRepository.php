<?php

namespace App\Repository;

use App\Entity\Dog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dog::class);
    }

    public function findRandomDog()
    {
        // Utilisation du QueryBuilder pour une approche plus intégrée
        $qb = $this->createQueryBuilder('d')
            ->orderBy('RANDOM()')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
