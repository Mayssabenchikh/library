<?php
namespace App\Controller;

use App\Entity\Genre;
use App\Form\GenreType;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/genre')]
final class GenreController extends AbstractController
{
    #[Route(name: 'app_genre_index', methods: ['GET'])]
public function index(GenreRepository $genreRepository, Request $request): Response
{
    $genres = $genreRepository->findAllWithBooks();
    $itemsPerPage = 4;
    $paginatedGenres = [];
    
    foreach ($genres as $genre) {
        $pageKey = 'page_' . $genre->getId();
        $currentPage = $request->query->getInt($pageKey, 1);
        $livres = $genre->getLivres();
        
        $totalItems = count($livres);
        $totalPages = max(1, ceil($totalItems / $itemsPerPage));
        $currentPage = min(max(1, $currentPage), $totalPages);
        
        $offset = ($currentPage - 1) * $itemsPerPage;
        $paginatedLivres = array_slice($livres, $offset, $itemsPerPage);
        
        $paginatedGenres[] = [
            'genre' => $genre,
            'livres' => $paginatedLivres,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'pageKey' => $pageKey,
            'hasPrevious' => $currentPage > 1,
            'hasNext' => $currentPage < $totalPages
        ];
    }
    
    return $this->render('genre/index.html.twig', [
        'paginatedGenres' => $paginatedGenres,
    ]);
}
    #[Route('/new', name: 'app_genre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($genre);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le genre a été créé avec succès');
            return $this->redirectToRoute('app_genre_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('genre/new.html.twig', [
            'genre' => $genre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_genre_show', methods: ['GET'])]
    public function show(Genre $genre): Response
    {
        return $this->render('genre/show.html.twig', [
            'genre' => $genre,
            'livres' => $genre->getLivres()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_genre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Genre $genre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Le genre a été modifié avec succès');
            return $this->redirectToRoute('app_genre_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('genre/edit.html.twig', [
            'genre' => $genre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_genre_delete', methods: ['POST'])]
    public function delete(Request $request, Genre $genre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$genre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($genre);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le genre a été supprimé avec succès');
        }
        
        return $this->redirectToRoute('app_genre_index', [], Response::HTTP_SEE_OTHER);
    }
}