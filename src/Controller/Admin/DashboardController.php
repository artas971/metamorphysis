<?php

namespace App\Controller\Admin;

use App\Controller\Admin\PrestationCrudController;
use App\Controller\Admin\ReservationCrudController;
use App\Controller\Admin\UserCrudController;
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
        return $this->redirect($adminUrlGenerator->setController(PrestationCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Metamorphysis - Espace Premium');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        
        yield MenuItem::section('Gestion du Cabinet');
        
        // CORRECTION EASYADMIN 5 : On utilise linkTo() avec le Controller en premier
        yield MenuItem::linkTo(ReservationCrudController::class, 'Réservations', 'fas fa-calendar-check');
        yield MenuItem::linkTo(PrestationCrudController::class, 'Prestations', 'fas fa-list');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fas fa-users');
        
        yield MenuItem::section('Site Web');
        yield MenuItem::linkToUrl('Retour au site', 'fas fa-globe', '/');
    }
}