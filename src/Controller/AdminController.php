<?php
namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\LivreRepository;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class AdminController extends AbstractController
{
#[Route('/admin', name: 'app_admin')]
public function index(
LivreRepository $livreRepository,
UserRepository $userRepository,
EmpruntRepository $empruntRepository
): Response {
$totalLivres = $livreRepository->count([]);
$totalUsers = $userRepository->count([]);
$totalEmprunts = $empruntRepository->count([]);
return $this->render('admin/dashboard.html.twig', [
'totalLivres' => $totalLivres,
'totalusers' => $totalUsers,
'totalemprunts' => $totalEmprunts,
]);
}
#[Route('/admin/users', name: 'app_admin_users')]
public function listUsers(UserRepository $userRepository): Response
{
$users = $userRepository->findAll();
if (!$users) {
$this->addFlash('warning', 'Aucun utilisateur trouvé');
}
return $this->render('admin/users-adm.html.twig', [
'users' => $users,
]);
}
#[Route('/admin/user/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
public function deleteUser(
User $user,
EntityManagerInterface $em,
EmpruntRepository $empruntRepository,
Request $request

): Response {
$submittedToken = $request->request->get('_token');
if (!$this->isCsrfTokenValid('delete' . $user->getId(), $submittedToken)) {
return $this->json(['success' => false, 'message' => 'Token CSRF invalide']);
}
try {
$emprunts = $empruntRepository->findBy(['utilisateur' => $user]);
foreach ($emprunts as $emprunt) {
$em->remove($emprunt);
}
$em->remove($user);
$em->flush();
return $this->json(['success' => true, 'message' => 'Utilisateur supprimé']);
} catch (\Exception $e) {
return $this->json(['success' => false, 'message' => 'Erreur lors de la suppression']);
}
}
}