<?php

namespace App\Controller;

use App\Entity\Deal;
use App\Repository\DealRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/moderator')]
class ModeratorController extends AbstractController
{
    #[Route('/', name: 'moderator_redirect_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function redirectToDashboard(): Response
    {
        return $this->redirectToRoute('moderator_dashboard');
    }

    #[Route('/dashboard', name: 'moderator_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function dashboard(): Response
    {
        return $this->render('moderator/dashboard.html.twig');
    }

    #[Route('/deals', name: 'moderator_deals', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function manageDeals(DealRepository $dealRepository): Response
    {
        $deals = $dealRepository->findAll();
        return $this->render('moderator/deals.html.twig', ['deals' => $deals]);
    }

    #[Route('/deals/{id}', name: 'moderator_deal_detail', methods: ['GET'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function viewDeal(Deal $deal): Response
    {
        return $this->render('moderator/deal_detail.html.twig', ['deal' => $deal]);
    }

    #[Route('/deals/approve/{id}', name: 'moderator_approve_deal', methods: ['POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function approveDeal(Deal $deal, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('approve' . $deal->getId(), $request->request->get('_token'))) {
            $deal->setIsActive(true);
            $entityManager->flush();
            $this->addFlash('success', 'Deal approuvé avec succès.');
        }

        return $this->redirectToRoute('moderator_deals');
    }

    #[Route('/deals/reject/{id}', name: 'moderator_reject_deal', methods: ['POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function rejectDeal(Deal $deal, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('reject' . $deal->getId(), $request->request->get('_token'))) {
            $deal->setIsActive(false);
            $entityManager->flush();
            $this->addFlash('success', 'Deal désactivé avec succès.');
        }

        return $this->redirectToRoute('moderator_deals');
    }

    #[Route('/deals/expire/{id}', name: 'moderator_expire_deal', methods: ['POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function expireDeal(Deal $deal, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('expire' . $deal->getId(), $request->request->get('_token'))) {
            $deal->setIsPublished(false);
            $entityManager->flush();
            $this->addFlash('success', 'Deal marqué comme expiré avec succès.');
        }

        return $this->redirectToRoute('moderator_deals');
    }

    #[Route('/deals/publish/{id}', name: 'moderator_publish_deal', methods: ['POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function publishDeal(Deal $deal, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('publish' . $deal->getId(), $request->request->get('_token'))) {
            $deal->setIsPublished(true);
            $entityManager->flush();
            $this->addFlash('success', 'Deal publié avec succès.');
        }

        return $this->redirectToRoute('moderator_deals');
    }
}
