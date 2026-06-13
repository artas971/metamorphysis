<?php

namespace App\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    // On utilise "/page/{slug}" pour éviter de casser tes autres routes comme /login ou /register
    #[Route('/page/{slug}', name: 'app_page_show')]
        public function show(string $slug, EntityManagerInterface $entityManager): Response
        {
            // On cherche la page dans la base de données grâce à son slug
            $page = $entityManager->getRepository(Page::class)->findOneBy(['slug' => $slug]);

            // LA SÉCURITÉ EST ICI : Si la page n'existe pas (!page) OU qu'elle n'est pas publiée
            if (!$page || !$page->isPublished()) {
                    throw $this->createNotFoundException('Cette page est en cours de rédaction ou n\'existe pas.');
            }

            return $this->render('page/show.html.twig', [
                'page' => $page,
            ]);
        }
}