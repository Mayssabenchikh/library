<?php
namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    public function findAllWithBooks(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.livre', 'l')
            ->addSelect('l')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySearchQuery(string $query): QueryBuilder
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.livre', 'l')
            ->addSelect('l')
            ->where('g.nom LIKE :query OR g.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('g.nom', 'ASC');
    }
}
