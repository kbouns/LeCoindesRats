<?php

namespace App\Controller;

use App\Form\PasswordUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        $user = $this->getUser(); // Ensure this returns a User object

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
        $user = $this->getUser(); // Ensure this returns a User object

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your account details.');
        }

        return $this->render('account/details.html.twig', [
            'user' => $user
        ]);
    }
}
