<?php

namespace App\Controller;

use App\Entity\Deal;
use App\Entity\Vote;
use App\Form\DealType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\DealRepository;
use App\Repository\VoteRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function show(Deal $deal, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository, VoteRepository $voteRepository): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
            $comment->setDeal($deal);
            $comment->setCommentTime(new \DateTime());
    
            $entityManager->persist($comment);
            $entityManager->flush();
    
            $this->addFlash('success', 'Commentaire ajouté avec succès.');
    
            return $this->redirectToRoute('deal_show', ['id' => $deal->getId()]);
        }
    
        $comments = $commentRepository->findBy(['deal' => $deal, 'parent' => null]);
        $upvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'upvote']);
        $downvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'downvote']);
    
        return $this->render('deal/show.html.twig', [
            'deal' => $deal,
            'form' => $form->createView(),
            'comments' => $comments,
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);
    }

    #[Route('/deal/{id}/upvote', name: 'deal_upvote', methods: ['POST'])]
    #[Route('/deal/{id}/downvote', name: 'deal_downvote', methods: ['POST'])]
    public function vote(Deal $deal, EntityManagerInterface $entityManager, VoteRepository $voteRepository, Request $request): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $route = $request->attributes->get('_route');
        $typeVote = ($route === 'deal_upvote') ? 'upvote' : 'downvote';
    
        $vote = $voteRepository->findOneBy(['deal' => $deal, 'user' => $user]);
    
        if ($vote) {
            if ($vote->getTypeVote() === $typeVote) {
                $entityManager->remove($vote);
            } else {
                $vote->setTypeVote($typeVote);
                $entityManager->persist($vote);
            }
        } else {
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setDeal($deal);
            $vote->setTypeVote($typeVote);
            $entityManager->persist($vote);
        }
    
        $entityManager->flush();
    
        return $this->redirectToRoute('deal_show', ['id' => $deal->getId()]);
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
}