<?php

namespace App\Controller;

use App\Entity\Deal;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\DealType;
use App\Repository\DealRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class DealController extends AbstractController
{
    #[Route('/deal', name: 'deal_index', methods: ['GET'])]
    public function index(DealRepository $dealRepository): Response
    {
        $deals = $dealRepository->findAll();

        return $this->render('deal/index.html.twig', [
            'deals' => $deals,
        ]);
    }

    #[Route('/deal/new', name: 'deal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $deal = new Deal();
        $form = $this->createForm(DealType::class, $deal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFilename')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $deal->setImageFilename($newFilename);
                    $this->addFlash('success', "L'image a été enregistrée.");
                } catch (FileException $e) {
                    $this->addFlash('error', "Problème lors de l'enregistrement de l'image.");
                }
            }

            $deal->setUser($this->getUser());

            $entityManager->persist($deal);
            $entityManager->flush();

            $this->addFlash('success', 'Le deal a été créé avec succès.');

            return $this->redirectToRoute('deal_index');
        }

        return $this->render('deal/new.html.twig', [
            'deal' => $deal,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deal/{id}', name: 'deal_show', methods: ['GET', 'POST'])]
    public function show(Deal $deal, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
            $comment->setDeal($deal);
            $comment->setCommenttime(new \DateTime());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');

            return $this->redirectToRoute('deal_show', ['id' => $deal->getId()]);
        }

        return $this->render('deal/show.html.twig', [
            'deal' => $deal,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deal/{id}/edit', name: 'deal_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Deal $deal, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(DealType::class, $deal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFilename')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $deal->setImageFilename($newFilename);
                    $this->addFlash('success', "L'image a été enregistrée.");
                } catch (FileException $e) {
                    $this->addFlash('error', "Problème lors de l'enregistrement de l'image.");
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le deal a été modifié avec succès.');

            return $this->redirectToRoute('app_account_deal_history');
        }

        return $this->render('deal/edit.html.twig', [
            'deal' => $deal,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deal/{id}/delete', name: 'deal_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Deal $deal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $deal->getId(), $request->request->get('_token'))) {
            $entityManager->remove($deal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_account_deal_history');
    }

    #[Route('/deal/{id}/comment/{commentId}/reply', name: 'comment_reply', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function reply(Request $request, Deal $deal, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $reply = new Comment();
        $form = $this->createForm(CommentType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reply->setUser($this->getUser());
            $reply->setDeal($deal);
            $reply->setCommenttime(new \DateTime());
            $reply->setCommentaire($comment);

            $entityManager->persist($reply);
            $entityManager->flush();

            $this->addFlash('success', 'Réponse ajoutée avec succès.');

            return $this->redirectToRoute('deal_show', ['id' => $deal->getId()]);
        }

        return $this->render('comment/reply.html.twig', [
            'form' => $form->createView(),
            'deal' => $deal,
            'comment' => $comment,
        ]);
    }
}