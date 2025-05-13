<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\PanierRepository;
use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        SessionInterface $session,
        PanierRepository $panierRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Si l'utilisateur est déjà connecté, redirigez-le
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Gestion du panier lors de la connexion
        if ($request->isMethod('POST')) {
            $user = $this->getUser();
            if ($user) {
                $panierSession = $session->get('panier', []);
                
                if (!empty($panierSession)) {
                    // Récupérer ou créer un panier pour l'utilisateur
                    $panier = $panierRepository->findOneBy(['user' => $user]) ?? new Panier();
                    $panier->setUser($user);
                    
                    // Fusionner le panier session avec le panier utilisateur
                    foreach ($panierSession as $livreId => $quantite) {
                        $panier->addItem($livreId, $quantite);
                    }
                    
                    $entityManager->persist($panier);
                    $entityManager->flush();
                    
                    // Vider le panier session
                    $session->remove('panier');
                }
            }
        }

        // Gestion des erreurs de connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): void
    {
        // Sauvegarder le panier dans la session avant déconnexion
        // (Cette partie sera interceptée par le firewall)
        
        // Note: La logique réelle de sauvegarde devrait être dans un EventSubscriber
        // pour le événement security.interactive_login et security.logout
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}