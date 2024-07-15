<?php

namespace App\Controller;

use App\Entity\Deal;
use App\Repository\DealRepository;
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
    public function index(Request $request, DealRepository $dealRepository): Response
    {
        $searchTerm = $request->query->get('search', '');

        if ($searchTerm) {
            $deals = $dealRepository->searchDeals($searchTerm);
        } else {
            $deals = $dealRepository->findBy([], ['publicationdate' => 'DESC']);
        }

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
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/deal/{id}/vote', name: 'deal_vote', methods: ['POST'])]
    public function vote(Deal $deal, Request $request, VoteRepository $voteRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => "Vous n'êtes pas connecté. Connectez-vous !"], 401);
        }

        $voteType = $request->request->get('vote_type');
        $entityManager = $this->managerRegistry->getManager();

        // Vérifiez si l'utilisateur a déjà voté pour ce deal
        $existingVote = $voteRepository->findOneBy(['deal' => $deal, 'user' => $user]);

        if ($existingVote) {
            // L'utilisateur a déjà voté, retournez une erreur
            return new JsonResponse(['error' => 'Vous avez déjà voté pour ce deal.'], 400);
        }

        // Créez un nouveau vote
        $vote = new Vote();
        $vote->setUser($user);
        $vote->setDeal($deal);
        $vote->setTypeVote($voteType);
        $entityManager->persist($vote);
        $entityManager->flush();

        $upvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'upvote']);
        $downvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'downvote']);

        return new JsonResponse([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);
    }
}
