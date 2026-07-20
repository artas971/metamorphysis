<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Form\ProfileEditType;
use App\Repository\SeanceRepository; // MODIFICATION : On utilise SeanceRepository
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    public function index(SeanceRepository $seanceRepository): Response
    {
        $user = $this->getUser();

        // On récupère toutes les séances de l'utilisateur, triées par Prestation puis par Numéro (1, 2, 3...)
        $seances = $seanceRepository->findBy(
            ['user' => $user],
            ['prestation' => 'ASC', 'numero' => 'ASC']
        );

        // On regroupe les séances par Prestation pour créer l'affichage "Parcours"
        $parcoursList = [];
        foreach ($seances as $seance) {
            $prestId = $seance->getPrestation()->getId();
            
            if (!isset($parcoursList[$prestId])) {
                $parcoursList[$prestId] = [
                    'prestation' => $seance->getPrestation(),
                    'seances' => [],
                    'total' => $seance->getPrestation()->getNombreSeances() ?? 1,
                    'planifiees' => 0
                ];
            }
            
            $parcoursList[$prestId]['seances'][] = $seance;
            
            // On compte combien de séances ont déjà une date
            if ($seance->getDateRendezVous() !== null) {
                $parcoursList[$prestId]['planifiees']++;
            }
        }

        return $this->render('account/index.html.twig', [
            'parcoursList' => $parcoursList,
        ]);
    }

    #[Route('/mon-compte/modifier', name: 'app_account_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
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
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }
        
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }   
}