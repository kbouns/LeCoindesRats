<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Deal;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    #[Route('/deal/{id}/comment/new', name: 'comment_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, Deal $deal, EntityManagerInterface $entityManager): Response
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

        return $this->render('comment/new.html.twig', [
            'form' => $form->createView(),
            'deal' => $deal,
        ]);
    }

    #[Route('/deal/{dealId}/comment/{commentId}/reply', name: 'comment_reply', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function reply(Request $request, EntityManagerInterface $entityManager, $dealId, $commentId): Response
    {
        $deal = $entityManager->getRepository(Deal::class)->find($dealId);
        $comment = $entityManager->getRepository(Comment::class)->find($commentId);

        if (!$deal || !$comment) {
            throw $this->createNotFoundException('Deal or Comment not found.');
        }

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