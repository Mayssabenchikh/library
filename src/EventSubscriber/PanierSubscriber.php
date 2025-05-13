<?php

// src/EventSubscriber/PanierSubscriber.php
namespace App\EventSubscriber;

use App\Entity\Panier;
use App\Entity\User;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class PanierSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private PanierRepository $panierRepository,
        private EntityManagerInterface $em
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLogin',
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof User) {
            return;
        }

        $session = $this->requestStack->getSession();
        $panierSession = $session->get('panier', []);

        // Récupère le panier existant ou crée un nouveau
        $panier = $this->panierRepository->findOneBy(['user' => $user]);
        
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $this->em->persist($panier);
        }

        // Fusionne les items du panier session avec le panier utilisateur
        $currentItems = $panier->getItems();
        foreach ($panierSession as $livreId => $quantity) {
            $currentItems[$livreId] = ($currentItems[$livreId] ?? 0) + $quantity;
        }

        $panier->setItems($currentItems);
        $this->em->flush();

        // Met à jour la session avec les données fusionnées
        $session->set('panier', $panier->getItems());
        $session->set('panier_count', array_sum($panier->getItems()));
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        if (!$user instanceof User) {
            return;
        }

        $session = $this->requestStack->getSession();
        $panierSession = $session->get('panier', []);
        
        if (!empty($panierSession)) {
            $panier = $this->panierRepository->findOneBy(['user' => $user]);
            
            if (!$panier) {
                $panier = new Panier();
                $panier->setUser($user);
            }
            
            $panier->setItems($panierSession);
            $this->em->persist($panier);
            $this->em->flush();
        }
    }
}