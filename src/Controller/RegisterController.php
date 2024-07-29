<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                if (!$form->get('acceptCharte')->getData()) {
                    $this->addFlash('error', 'Vous devez accepter la charte numérique pour vous inscrire.');
                    return $this->render('register/index.html.twig', [
                        'Registerform' => $form->createView()
                    ]);
                }

     
                $entityManager->persist($user);
                $entityManager->flush();

         
                $this->addFlash('success', 'Inscription réussie !');
                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('register/index.html.twig', [
            'Registerform' => $form->createView()
        ]);
    }
}
