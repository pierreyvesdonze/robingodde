<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll(); // récupère les projets
        $randomProject = null;

        if (!empty($projects)) {
            $randomProject = $projects[array_rand($projects)]; // choisit un projet au hasard
        }

        return $this->render('home/home.html.twig', [
            'randomProject' => $randomProject,
        ]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET'])]
    public function contact(): Response
    {
        return $this->render('contact/contact.html.twig', []);
    }
}
