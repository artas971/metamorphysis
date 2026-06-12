<?php

namespace App\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    // On utilise "/page/{slug}" pour éviter de casser tes autres routes comme /login ou /register
    #[Route('/page/{slug}', name: 'app_page_show')]
    public function show(string $slug, EntityManagerInterface $entityManager): Response
    {
        // On cherche la page dans la base de données grâce à son slug
        $page = $entityManager->getRepository(Page::class)->findOneBy(['slug' => $slug]);

        // Si la page n'existe pas (le client a tapé une mauvaise URL), on renvoie une erreur 404
        if (!$page) {
            throw $this->createNotFoundException('Cette page n\'existe pas.');
        }

        // On envoie les données de la page à la vue Twig
        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }
}