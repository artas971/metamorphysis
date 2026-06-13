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
}