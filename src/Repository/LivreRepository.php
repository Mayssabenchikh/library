<?php
// src/Repository/BookRepository.php
namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    /**
     * Recherche de livres par titre, nom d'auteur ou genre
     */
    public function search(string $query)
    {
        $qb = $this->createQueryBuilder('b')
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