<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    public function search(string $query)
    {
        $qb = $this->createQueryBuilder('b')
            ->addSelect('a', 'g')
            ->leftJoin('b.auteur', 'a')
            ->leftJoin('b.genre', 'g')
            ->where('b.titre LIKE :query')
            ->orWhere('a.nom LIKE :query')
            ->orWhere('g.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('b.titre', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
