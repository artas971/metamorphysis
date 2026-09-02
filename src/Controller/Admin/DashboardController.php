<?php

namespace App\Controller\Admin;

use App\Controller\Admin\HoraireHebdomadaireCrudController;
use App\Controller\Admin\IndisponibiliteCrudController;
use App\Controller\Admin\PageCrudController;
use App\Controller\Admin\PrestationCrudController;
use App\Controller\Admin\SeanceCrudController;
use App\Controller\Admin\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'url_reservations' => '/admin/seance',
            'url_pages' => '/admin/page',
            'url_prestations' => '/admin/prestation',
        ]);        
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
        yield MenuItem::linkToUrl('Suivi des Séances', 'fas fa-calendar-check', '/admin/seance');
        yield MenuItem::linkToUrl('Mes Prestations', 'fa-solid fa-gem', '/admin/prestation');
        yield MenuItem::linkToUrl('Contenu des Pages', 'fa-solid fa-file-lines', '/admin/page');
        yield MenuItem::linkToUrl('Clients', 'fa-solid fa-user-friends', '/admin/user');

        yield MenuItem::section('Planning & Horaires');
        yield MenuItem::linkToUrl('Ma Semaine Type', 'fa-solid fa-clock', '/admin/horaire-hebdomadaire');
        yield MenuItem::linkToUrl('Mes Congés & Fermetures', 'fa-solid fa-calendar-times', '/admin/indisponibilite');

        yield MenuItem::section('Site Web');
        yield MenuItem::linkToUrl('Retour au site public', 'fa-solid fa-arrow-left', '/');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addHtmlContentToHead('<style>
                :root {
                    --ea-color-primary: #b89a63 !important;
                }
                
                .btn-primary, 
                a.btn-primary, 
                button.btn-primary {
                    background: #b89a63 !important;
                    background-color: #b89a63 !important;
                    border-color: #b89a63 !important;
                    color: #ffffff !important;
                    box-shadow: none !important;
                    
                    --bs-btn-bg: #b89a63 !important;
                    --bs-btn-border-color: #b89a63 !important;
                    --bs-btn-hover-bg: #8a998b !important;
                    --bs-btn-hover-border-color: #8a998b !important;
                    --bs-btn-active-bg: #8a998b !important;
                }

                .btn-primary:hover, 
                a.btn-primary:hover, 
                button.btn-primary:hover {
                    background: #8a998b !important;
                    background-color: #8a998b !important;
                    border-color: #8a998b !important;
                    color: #ffffff !important;
                }

                .form-switch .form-check-input:checked {
                    background-color: #b89a63 !important;
                    border-color: #b89a63 !important;
                }

                .badge-primary, .badge-success { background-color: #b89a63 !important; color: #fff !important; }
                .main-header { border-bottom: 2px solid #b89a63 !important; }

                .ea-lightbox-thumbnail, 
                .field-collection-item-action,
                [class*="field-collection"] ul li,
                td .form-img-container {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    vertical-align: middle !important;
                }

                .ea-lightbox-thumbnail img, 
                .field-collection img,
                td .form-img-container img {
                    object-fit: cover !important;
                    object-position: center !important;
                    width: 24px !important;
                    height: 24px !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    display: inline-block !important;
                    border-radius: 3px !important;
                }
            </style>');
    }
}