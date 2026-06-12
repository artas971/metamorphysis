<?php

namespace App\Controller\Admin;

use App\Controller\Admin\PrestationCrudController;
use App\Controller\Admin\ReservationCrudController;
use App\Controller\Admin\UserCrudController; 
use App\Controller\Admin\PageCrudController;  
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
 
#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        
        // Redirection par défaut vers les réservations
        return $this->redirect($adminUrlGenerator->setController(ReservationCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            // Titre premium avec balises HTML pour correspondre à ta DA
            ->setTitle('<b>METAMORPHYSIS</b><br><span style="font-size: 0.75rem; letter-spacing: 1px; opacity: 0.7;">ESPACE PREMIUM</span>')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
        {
            // Accueil de l'administration
            yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
            
            yield MenuItem::section('Gestion du Cabinet');
            
            // Utilisation stricte de la syntaxe qui fonctionne sur ta version d'EasyAdmin
            yield MenuItem::linkTo(ReservationCrudController::class, 'Réservations', 'fas fa-calendar-check');
            yield MenuItem::linkTo(PrestationCrudController::class, 'Prestations', 'fas fa-gem');
            
            // CORRECTION ICI : On utilise linkTo() avec le PageCrudController
            yield MenuItem::linkTo(PageCrudController::class, 'Pages (Textes)', 'fas fa-file-alt');
            
            yield MenuItem::linkTo(UserCrudController::class, 'Clients', 'fas fa-users');
            
            yield MenuItem::section('Site Web');
            yield MenuItem::linkToUrl('Retour au site public', 'fas fa-arrow-left', '/');
        }
}