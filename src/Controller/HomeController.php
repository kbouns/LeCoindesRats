<?php

namespace App\Controller;

use App\Entity\Deal;
use App\Repository\DealRepository;
use App\Repository\VoteRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(Request $request, DealRepository $dealRepository, VoteRepository $voteRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search', '');

        if ($searchTerm) {
            $queryBuilder = $dealRepository->searchDealsQueryBuilder($searchTerm);
        } else {
            $queryBuilder = $dealRepository->createQueryBuilder('d')
                ->orderBy('d.publicationdate', 'DESC');
        }

        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $dealsWithVotes = [];
        foreach ($pagination as $deal) {
            $upvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'upvote']);
            $downvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'downvote']);
            $dealsWithVotes[] = [
                'deal' => $deal,
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
            ];
        }

        // Calcul des best deals indépendamment de la recherche
        $allDeals = $dealRepository->findAll();
        $allDealsWithVotes = [];
        foreach ($allDeals as $deal) {
            $upvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'upvote']);
            $downvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'downvote']);
            $allDealsWithVotes[] = [
                'deal' => $deal,
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
            ];
        }

        usort($allDealsWithVotes, function ($a, $b) {
            return $b['upvotes'] <=> $a['upvotes'];
        });

        $bestDeals = array_slice($allDealsWithVotes, 0, 3);

        return $this->render('home/index.html.twig', [
            'deals' => $pagination, // Cette ligne passe la variable deals à la vue
            'dealsWithVotes' => $dealsWithVotes,
            'bestDeals' => $bestDeals,
            'searchTerm' => $searchTerm,
            'pagination' => $pagination,
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

    #[Route('/best-deals', name: 'best_deals')]
    public function bestDeals(Request $request, DealRepository $dealRepository, VoteRepository $voteRepository, PaginatorInterface $paginator): Response
    {
        $allDeals = $dealRepository->findAll();
        $allDealsWithVotes = [];
        foreach ($allDeals as $deal) {
            $upvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'upvote']);
            $downvotes = $voteRepository->count(['deal' => $deal, 'typeVote' => 'downvote']);
            $allDealsWithVotes[] = [
                'deal' => $deal,
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
            ];
        }

        usort($allDealsWithVotes, function ($a, $b) {
            return $b['upvotes'] <=> $a['upvotes'];
        });

        $pagination = $paginator->paginate(
            $allDealsWithVotes, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('home/best_deals.html.twig', [
            'bestDeals' => $pagination,
        ]);
    }
}
