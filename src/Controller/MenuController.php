<?php

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    public function renderDynamicNav(PageRepository $pageRepository): Response
        {
            // On exige afficherMenu = true ET isPublished = true
            $pages = $pageRepository->findBy([
                'afficherMenu' => true,
                'isPublished' => true
            ]);

            return $this->render('partials/_nav_links.html.twig', [
                'pages' => $pages,
            ]);
        }
        public function renderDynamicFooter(PageRepository $pageRepository): Response
    {
        // On cherche les pages publiées MAIS qui ne sont pas dans le menu principal
        $pages = $pageRepository->findBy([
            'isPublished' => true,
            'afficherMenu' => false
        ]);

        return $this->render('partials/_footer_links.html.twig', [
            'pages' => $pages,
        ]);
    }
}