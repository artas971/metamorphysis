<?php

namespace App\Controller;

use App\Repository\PrestationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PrestationRepository $prestationRepository): Response
    {
        // On va chercher toutes les prestations dans la base de données
        $prestations = $prestationRepository->findAll();

        // On les envoie à notre fichier Twig sous le nom 'prestations'
        return $this->render('home/index.html.twig', [
            'prestations' => $prestations,
        ]);
    }
    #[Route('/accueil', name: 'app_accueil')]
        public function accueil(PrestationRepository $prestationRepository): Response
        {
            // On récupère toutes les prestations actives dans la base de données
            $prestations = $prestationRepository->findAll();

            return $this->render('home/accueil.html.twig', [
                'prestations' => $prestations, // On envoie les données à la page
            ]);
        }
}