<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrestationController extends AbstractController
{
    // Le paramètre {slug} rend l'URL dynamique (ex: /prestation/massage)
    #[Route('/prestation/{slug}', name: 'app_prestation_show')]
    public function show(string $slug): Response
    {
        // Plus tard, nous irons chercher les vraies infos dans la base de données grâce à ce slug.
        // Pour l'instant, on envoie simplement le slug au fichier Twig pour l'afficher.
        return $this->render('prestation/show.html.twig', [
            'slug' => $slug,
        ]);
    }
}