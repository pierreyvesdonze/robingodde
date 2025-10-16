<?php

namespace App\Controller;

use App\Entity\Text;
use App\Form\TextType;
use App\Repository\TextRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/text')]
final class TextController extends AbstractController
{
    #[Route(name: 'app_text_index', methods: ['GET'])]
    public function index(TextRepository $textRepository): Response
    {
        return $this->render('text/index.html.twig', [
            'texts' => $textRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_text_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $text = new Text();
        $form = $this->createForm(TextType::class, $text);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($text);
            $entityManager->flush();

            return $this->redirectToRoute('app_text_show', [
                'id' => $text->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('text/new.html.twig', [
            'text' => $text,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_text_show', methods: ['GET'])]
    public function show(Text $text): Response
    {
        return $this->render('text/show.html.twig', [
            'text' => $text,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_text_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Text $text, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TextType::class, $text);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_text_show', [
                'id' => $text->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('text/edit.html.twig', [
            'text' => $text,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_text_delete', methods: ['POST'])]
    public function delete(Request $request, Text $text, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $text->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($text);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_text_index', [], Response::HTTP_SEE_OTHER);
    }
}
