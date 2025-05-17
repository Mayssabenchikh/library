<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileType;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccountController extends AbstractController
{
    /**
     * Affiche la page du compte utilisateur
     */
    #[Route('/account', name: 'app_account')]
    public function index(EmpruntRepository $empruntRepository): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('account/my-account.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Traite la mise à jour des informations du profil utilisateur
     */
    #[Route('/account/update', name: 'app_user_update', methods: ['POST'])]
    public function updateProfile(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Vérifier si l'utilisateur est connecté
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer les données du formulaire
        $nom = $request->request->get('nom');
        $email = $request->request->get('email');
        $telephone = $request->request->get('telephone');
        $password = $request->request->get('password');
        $passwordConfirm = $request->request->get('password_confirm');

        // Mettre à jour les informations de base
        $user->setNom($nom);
        $user->setEmail($email);
        $user->setTelephone($telephone);

        // Gérer le changement de mot de passe si fourni
        if (!empty($password)) {
            // Vérifier que les deux mots de passe correspondent
            if ($password !== $passwordConfirm) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_account');
            }

            // Hasher et définir le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
        }

        // Enregistrer les modifications
        $entityManager->persist($user);
        $entityManager->flush();

        // Ajouter un message flash de succès
        $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

        // Rediriger vers la page du compte
        return $this->redirectToRoute('app_account');
    }
}