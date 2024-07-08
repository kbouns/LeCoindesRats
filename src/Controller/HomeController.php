<?php

namespace App\Controller;

use App\Entity\Deal;
use App\Entity\Categorie;
use App\Repository\DealRepository;
use App\Repository\CategorieRepository;
use Doctrine\Persistence\ManagerRegistry;
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
        $deals = $dealRepository->findAll();

        return $this->render('home/index.html.twig', [
            'deals' => $deals,
        ]);
    }
}