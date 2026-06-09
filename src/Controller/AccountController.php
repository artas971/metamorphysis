<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Cette ligne bloque l'accès à toute personne qui n'est pas connectée
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        // On récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // On va chercher toutes SES réservations dans la base de données, 
        // triées par date (la plus récente en premier)
        $reservations = $reservationRepository->findBy(
            ['user' => $user],
            ['dateRendezVous' => 'DESC']
        );

        return $this->render('account/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }
}