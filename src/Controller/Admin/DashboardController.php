<?php

namespace App\Controller\Admin;

use App\Controller\Admin\ReservationCrudController;
use App\Controller\Admin\PrestationCrudController;
use App\Controller\Admin\PageCrudController;
use App\Controller\Admin\UserCrudController;
use App\Controller\Admin\HoraireHebdomadaireCrudController;
use App\Controller\Admin\IndisponibiliteCrudController;

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
            ->setTitle('<b>METAMORPHYSIS</b><br><span style="font-size: 0.75rem; letter-spacing: 1px; opacity: 0.7;">ESPACE PREMIUM</span>')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        
        yield MenuItem::section('Gestion du Cabinet');
        
        // La VRAIE syntaxe moderne : Controller, Titre, Icône
        yield MenuItem::linkTo(ReservationCrudController::class, 'Réservations', 'fas fa-calendar-check');
        yield MenuItem::linkTo(PrestationCrudController::class, 'Prestations', 'fas fa-gem');
        yield MenuItem::linkTo(PageCrudController::class, 'Pages (Textes)', 'fas fa-file-alt');
        yield MenuItem::linkTo(UserCrudController::class, 'Clients', 'fas fa-users');
        
        yield MenuItem::section('Site Web');
        yield MenuItem::linkToUrl('Retour au site public', 'fas fa-arrow-left', '/');
        
        yield MenuItem::section('Planning & Réservations');
        yield MenuItem::linkTo(HoraireHebdomadaireCrudController::class, 'Ma Semaine Type', 'fas fa-clock');
        yield MenuItem::linkTo(IndisponibiliteCrudController::class, 'Mes Congés & Fermetures', 'fas fa-calendar-times');
    }
}