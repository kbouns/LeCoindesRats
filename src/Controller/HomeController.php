<?php

namespace App\Controller;

use App\Entity\Deal;
use App\Entity\Vote;
use App\Repository\VoteRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $dealRepository = $this->managerRegistry->getRepository(Deal::class);
        $deals = $dealRepository->findBy([], ['publicationdate' => 'DESC']);

        $dealsWithVotes = [];
        foreach ($deals as $deal) { 
            $upvotes = 0;
            $downvotes = 0;
            foreach ($deal->getVotes() as $vote) {
                if ($vote->getTypeVote() === 'upvote') {
                    $upvotes++;
                } elseif ($vote->getTypeVote() === 'downvote') {
                    $downvotes++;
                }
            }
            $dealsWithVotes[] = [
                'deal' => $deal,
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
            ];
        }

        usort($dealsWithVotes, function ($a, $b) {
            return $b['upvotes'] <=> $a['upvotes'];
        });

        $bestDeals = array_slice($dealsWithVotes, 0, 3);

        return $this->render('home/index.html.twig', [
            'dealsWithVotes' => $dealsWithVotes,
            'bestDeals' => $bestDeals,
        ]);
    }

    #[Route('/deal/{id}/vote', name: 'deal_vote', methods: ['POST'])]
    public function vote(Deal $deal, Request $request, VoteRepository $voteRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => "Vous n'êtes pas connecter, Connectez-vous !!! "], 401);
        }

        $voteType = $request->request->get('vote_type');
        $entityManager = $this->managerRegistry->getManager();

        $vote = $voteRepository->findOneBy(['deal' => $deal, 'user' => $user]);

        if ($vote) {
            if ($vote->getTypeVote() === $voteType) {
                $entityManager->remove($vote);
            } else {
                $vote->setTypeVote($voteType);
                $entityManager->persist($vote);
            }
        } else {
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setDeal($deal);
            $vote->setTypeVote($voteType);
            $entityManager->persist($vote);
        }

        $entityManager->flush();

        $upvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'upvote']);
        $downvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'downvote']);

        return new JsonResponse([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);
    }
}