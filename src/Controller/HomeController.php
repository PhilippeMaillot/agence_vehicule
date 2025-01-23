<?php

namespace App\Controller;

use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'vehicule' => $vehiculeRepository->findAll(),
        ]);
    }

    #[Route('/mesreservation', name: 'app_home_reservation')]
    public function reservation(): Response
    {
        return $this->render('home/reservation.html.twig');
    }


}
