<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\EncryptionService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

// On branche l'écouteur sur les différents moments de vie de l'entité User
#[AsEntityListener(event: Events::prePersist, method: 'onPrePersist', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'onPreUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postLoad, method: 'onPostLoad', entity: User::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onPostPersist', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onPostUpdate', entity: User::class)]
class UserEncryptionListener
{
    private EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    // Avant une création en BDD
    public function onPrePersist(User $user): void
    {
        $this->encryptUserData($user);
    }

    // Avant une modification en BDD
    public function onPreUpdate(User $user): void
    {
        $this->encryptUserData($user);
    }

    // Après avoir chargé l'utilisateur depuis la BDD (On remet en clair pour l'application)
    public function onPostLoad(User $user): void
    {
        $this->decryptUserData($user);
    }

    // Après un enregistrement, on remet les données en clair dans la mémoire de l'application
    // pour éviter qu'elles ne s'affichent chiffrées si la page se recharge directement
    public function onPostPersist(User $user): void
    {
        $this->onPostLoad($user); 
    }

    public function onPostUpdate(User $user): void
    {
        $this->onPostLoad($user);
    }

    private function encryptUserData(User $user): void
    {
        if ($user->getEmail()) {
            $user->setEmail($this->encryptionService->encrypt(mb_strtolower($user->getEmail()), true));
        }

        if ($user->getPrenom()) {
            $user->setPrenom($this->encryptionService->encrypt($user->getPrenom(), false));
        }

        if ($user->getNom()) {
            $user->setNom($this->encryptionService->encrypt($user->getNom(), false));
        }

        if ($user->getTelephone()) {
            $user->setTelephone($this->encryptionService->encrypt($user->getTelephone(), false));
        }
    }

    private function decryptUserData(User $user): void
    {
        if ($user->getEmail()) {
            $user->setEmail($this->encryptionService->decrypt($user->getEmail()));
        }

        if ($user->getPrenom()) {
            $user->setPrenom($this->encryptionService->decrypt($user->getPrenom()));
        }

        if ($user->getNom()) {
            $user->setNom($this->encryptionService->decrypt($user->getNom()));
        }

        if ($user->getTelephone()) {
            $user->setTelephone($this->encryptionService->decrypt($user->getTelephone()));
        }
    }
}