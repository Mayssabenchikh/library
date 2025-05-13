<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(LivreRepository $bookRepository): Response
    {
        // Récupération des derniers livres ajoutés (nouveautés)
        $featuredBooks = $bookRepository->findBy(
            [],
            ['id' => 'DESC'],
            4
        );
        return $this->render('home/index.html.twig', [
            'featured_books' => $featuredBooks,
        ]);
    }
}