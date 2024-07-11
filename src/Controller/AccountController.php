<?php

namespace App\Controller;

use App\Entity\Deal;
use App\Form\PasswordUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }

    #[Route('/compte/modifier-mot-de-passe', name: 'app_account_modify')]
    public function password(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); 
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to change your password.');
        }

        $form = $this->createForm(PasswordUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Password changed successfully.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('account/password.html.twig', [
            'modifyPwd' => $form->createView()
        ]);
    }

    #[Route('/compte/details', name: 'app_account_details')]
    public function details(): Response
    {
        $user = $this->getUser(); 
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your account details.');
        }

        return $this->render('account/details.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/compte/historique-deals', name: 'app_account_deal_history')]
    #[IsGranted('ROLE_USER')]
    public function dealHistory(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $deals = $entityManager->getRepository(Deal::class)->findBy(['user' => $user]);

        return $this->render('account/deal_history.html.twig', [
            'deals' => $deals
        ]);
    }

    #[Route('/compte/deal/{id}/toggle', name: 'app_account_toggle_deal')]
    #[IsGranted('ROLE_USER')]
    public function toggleDeal(Deal $deal, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($deal->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You cannot modify this deal.');
        }

        $deal->setIsActive(!$deal->getIsActive());
        $entityManager->persist($deal);
        $entityManager->flush();

        return $this->redirectToRoute('app_account_deal_history');
    }
}