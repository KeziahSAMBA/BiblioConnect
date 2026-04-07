<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Si déjà connecté, redirige vers le bon dashboard
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();

            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('admin_dashboard');
            }
            if (in_array('ROLE_LIBRARIAN', $roles)) {
                return $this->redirectToRoute('librarian_dashboard');
            }
            return $this->redirectToRoute('user_dashboard');
        }

        // Affiche la page d'accueil publique
        return $this->render('home/index.html.twig');
    }
}