<?php

// src/Controller/CommentController.php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Deal;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    #[Route('/deal/{id}', name: 'deal_show', methods: ['GET', 'POST'])]
    public function show(Deal $deal, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
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

        $comments = $commentRepository->findBy(['deal' => $deal, 'parent' => null]);

        return $this->render('deal/show.html.twig', [
            'deal' => $deal,
            'form' => $form->createView(),
            'comments' => $comments,
        ]);
    }

    #[Route('/deal/{dealId}/comment/{commentId}/reply', name: 'comment_reply', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function reply(Request $request, EntityManagerInterface $entityManager, int $dealId, int $commentId): Response
    {
        $deal = $entityManager->getRepository(Deal::class)->find($dealId);
        $comment = $entityManager->getRepository(Comment::class)->find($commentId);

        if (!$deal) {
            throw $this->createNotFoundException('Deal not found.');
        }

        if (!$comment) {
            throw $this->createNotFoundException('Comment not found.');
        }

        $reply = new Comment();
        $form = $this->createForm(CommentType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reply->setUser($this->getUser());
            $reply->setDeal($deal);
            $reply->setCommenttime(new \DateTime());
            $reply->setParent($comment);

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

