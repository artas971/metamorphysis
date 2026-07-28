<?php

namespace App\Controller;

use App\Repository\PageRepository;
use App\Repository\PrestationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // ======================================================
    // PAGE 1 : L'introduction avec le logo animé (Splash Screen)
    // ======================================================
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // On affiche juste la vue avec le logo animé
        // (Remplace 'home/splash.html.twig' par le vrai nom de ton fichier Twig avec le logo)
        return $this->render('home/index.html.twig');
    }

    // ======================================================
    // PAGE 2 : La vraie page d'accueil (Bascule Dynamique / Native)
    // ======================================================
    #[Route('/accueil', name: 'app_accueil')]
    public function accueil(PageRepository $pageRepository, PrestationRepository $prestationRepository): Response
    {
        // 1. On cherche la page personnalisée avec le slug 'accueil'
        $pageCustom = $pageRepository->findOneBy(['slug' => 'accueil']);

        // 2. On vérifie qu'elle existe ET qu'elle est publiée
        // (Vérifie toujours si c'est bien isPublished(), isPubliee(), etc. dans ton Entity/Page.php)
        if ($pageCustom && $pageCustom->isPublished()) {
            
            // On renvoie la vue de ton constructeur de page (Page Builder)
            return $this->render('page/show.html.twig', [
                'page' => $pageCustom,
            ]);
        }

        // 3. Fallback : Si le bouton est DÉCOCHÉ, on affiche ta page native optimisée
        $prestations = $prestationRepository->findAll();
        
        return $this->render('home/accueil.html.twig', [
            'prestations' => $prestations,
        ]);
    }
}
