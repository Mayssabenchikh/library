<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/livres') ]
final class LivreController extends AbstractController
{
    #[Route(name: 'app_livre_index', methods: ['GET'])]
    public function index(LivreRepository $livreRepository, Request $request): Response
    {
        if ($request->get('search')) {
            $livres = $livreRepository->search($request->get('search'));
        }else{
            $livres = $livreRepository->findAll();
        }
        return $this->render('livre/index.html.twig', [
            'livres' => $livres,
        ]);
    }

    #[Route('/new', name: 'app_livre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livre);
            $entityManager->flush();

            return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livre/new.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livre_show', methods: ['GET'])]
    public function show(Livre $livre): Response
    {
        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_livre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livre/edit.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livre_delete', methods: ['POST'])]
    public function delete(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($livre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search', name: 'app_livre_search', methods: ['GET'])]
    public function search(Request $request, LivreRepository $livreRepository): Response
    {
        $query = $request->query->get('query', '');
    
        $livres = $livreRepository->search($query);
    
        return $this->render('livre/search.html.twig', [
            'query' => $query,
            'livres' => $livres,
        ]);
    }
    
    #[Route('/livre/retourner/{id}', name: 'app_livre_retourner')]
public function retournerAction(Request $request, Emprunt $emprunt, EntityManagerInterface $entityManager): Response
{
    // Vérifier que l'utilisateur connecté est bien celui qui a emprunté le livre
    if ($emprunt->getUtilisateur() !== $this->getUser()) {
        $this->addFlash('error', 'Vous n\'êtes pas autorisé à retourner ce livre.');
        return $this->redirectToRoute('app_account');
    }
    
    // Vérifier que le livre n'a pas déjà été retourné
    if ($emprunt->getDateRetourEffective() !== null) {
        $this->addFlash('error', 'Ce livre a déjà été retourné.');
        return $this->redirectToRoute('app_account');
    }
    
    // Marquer comme retourné en enregistrant la date de retour effective
    $emprunt->setDateRetourEffective(new \DateTime());
    
    // Mettre à jour la disponibilité du livre
    $livre = $emprunt->getLivre();
    $livre->setDisponible(true);
    
    $entityManager->persist($emprunt);
    $entityManager->persist($livre);
    $entityManager->flush();
    
    $this->addFlash('success', 'Le livre a été retourné avec succès.');
    return $this->redirectToRoute('app_account');
}
    
}
