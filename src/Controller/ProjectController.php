<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Service\ImageResizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/works')]
final class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findBy([], ['date' => 'DESC']);

        foreach ($projects as $project) {
            $project->randomWidth = ['uk-width-1-3', 'uk-width-1-4', 'uk-width-1-2'][array_rand([0, 1, 2])];
            $project->randomHeight = ['200px', '250px', '300px', '350px', '400px'][array_rand([0, 1, 2, 3, 4])];
        }

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageResizer $imageResizer
    ): Response {
        $project = new Project();
        $form    = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mainImage     = $form->get('mainImage')->getData();
            $mainImagePath = null;

            if ($mainImage) {
                $mainImagePath = $imageResizer->resize($mainImage, $this->getParameter('images_directory'));
                $project->setMainImage($mainImagePath);
            }

            $images = $form->get('images')->getData();
            if ($images) {
                foreach ($images as $imageFile) {
                    $imagePath = $imageResizer->resize($imageFile, $this->getParameter('images_directory'));

                    $imageEntity = new Image();
                    $imageEntity->setPath($imagePath);
                    $project->addImage($imageEntity);
                    $entityManager->persist($imageEntity);
                }
            }

            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_show', [
                'id' => $project->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager,
        ImageResizer $imageResizer
    ): Response {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ---- Main Image ----
            $mainImageFile = $form->get('mainImage')->getData();
            if ($mainImageFile) {
                // Supprimer l’ancienne image si elle existe
                if ($project->getMainImage()) {
                    $oldMainPath = $this->getParameter('images_directory') . '/' . $project->getMainImage();
                    if (file_exists($oldMainPath)) {
                        unlink($oldMainPath);
                    }
                }

                $mainImagePath = $imageResizer->resize($mainImageFile, $this->getParameter('images_directory'));
                $project->setMainImage($mainImagePath);
            }

            // ---- Images multiples ----
            $imagesFiles = $form->get('images')->getData();
            if ($imagesFiles) {
                // Supprimer toutes les anciennes images liées
                foreach ($project->getImages() as $oldImage) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . $oldImage->getPath();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $project->removeImage($oldImage);
                    $entityManager->remove($oldImage);
                }

                // Ajouter les nouvelles images
                foreach ($imagesFiles as $file) {
                    $imagePath = $imageResizer->resize($file, $this->getParameter('images_directory'));
                    $imageEntity = new Image();
                    $imageEntity->setPath($imagePath);
                    $imageEntity->setProject($project);
                    $project->addImage($imageEntity);
                    $entityManager->persist($imageEntity);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_project_show', [
                'id' => $project->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_project_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $project->getId(), $request->request->get('_token'))) {

            $imagesDirectory = $this->getParameter('images_directory');

            // Supprimer la mainImage physique
            if ($project->getMainImage()) {
                $mainImagePath = $imagesDirectory . '/' . $project->getMainImage();
                if (file_exists($mainImagePath)) {
                    unlink($mainImagePath);
                }
            }

            // Supprimer toutes les images liées physiquement
            foreach ($project->getImages() as $image) {
                $path = $imagesDirectory . '/' . $image->getPath();
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            // Supprime le projet (les entités Image sont supprimées grâce à orphanRemoval)
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }
}
