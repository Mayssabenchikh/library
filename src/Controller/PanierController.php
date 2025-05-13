<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Repository\LivreRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/panier/add/{id}', name: 'app_panier_add')]
    public function add($id, SessionInterface $session, PanierRepository $panierRepository): Response
    {
        $panier = $session->get('panier', []);
        
        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }
        
        $session->set('panier', $panier);
        
        // Sauvegarde également en base si l'utilisateur est connecté
        if ($this->getUser()) {
            $userPanier = $panierRepository->findOneBy(['user' => $this->getUser()]) ?? new Panier();
            $userPanier->setUser($this->getUser());
            $userPanier->setItems($panier);
            
            $this->em->persist($userPanier);
            $this->em->flush();
        }

        return new Response(null, Response::HTTP_OK);
    }
    #[Route('/panier', name: 'app_panier')]
public function index(SessionInterface $session, LivreRepository $livreRepository): Response
{
    $panier = $session->get('panier', []);
    $total = 0;
    $panierData = [];
    foreach ($panier as $id => $quantity) {
        $livre = $livreRepository->find($id);
        if ($livre) {
            $panierData[] = [
                'livre' => $livre,
                'quantity' => $quantity
            ];
        }
        $total += $livre->getPrix() * $quantity;
    }
    
    return $this->render('panier/index.html.twig', [
        'items' => $panierData,
        'total' => $total
    ]);
}
#[Route('/panier/remove/{id}', name: 'app_panier_remove')]
public function remove($id, SessionInterface $session): Response
{
    $panier = $session->get('panier', []);
    
    if (!empty($panier[$id])) {
        unset($panier[$id]);
    }
    
    $session->set('panier', $panier);
    
    return $this->redirectToRoute('app_panier');
}
}