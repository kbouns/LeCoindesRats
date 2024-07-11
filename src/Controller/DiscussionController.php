<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiscussionController extends AbstractController
{
    private CommentRepository $commentRepository;

    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    #[Route('/discussions', name: 'commentaire')]
    public function index(): Response
    {

        $comments = $this->commentRepository->findAll();

        return $this->render('discussion/index.html.twig', [
            'comments' => $comments,
            'filter' => 'all',
        ]);
    }

    #[Route('/discussions/commented', name: 'discussion_commented')]
    public function commented(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
        }

        $comments = $this->commentRepository->findBy(['user' => $user]);

        return $this->render('discussion/index.html.twig', [
            'comments' => $comments,
            'filter' => 'commented',
        ]);
    }
}