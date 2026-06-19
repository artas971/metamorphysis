<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Form\ProfileEditType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
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

    #[Route('/mon-compte/modifier', name: 'app_account_edit')]
    public function edit(\Symfony\Component\HttpFoundation\Request $request, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations personnelles ont été mises à jour.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/mon-compte/mot-de-passe', name: 'app_password_change')]
    #[IsGranted('ROLE_USER')]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        // AJOUTE CETTE VÉRIFICATION STRICTE
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère le nouveau mot de passe tapé dans le formulaire
            $newPassword = $form->get('newPassword')->getData();

            // On le hash (le crypte)
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            
            // On l'applique à l'utilisateur
            $user->setPassword($hashedPassword);

            // On sauvegarde en base de données
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }   
}