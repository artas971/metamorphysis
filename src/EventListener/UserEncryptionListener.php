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
        if ($user->getTelephone()) {
            $user->setTelephone($this->encryptionService->encrypt($user->getTelephone()));
        }
    }

    // Avant une modification en BDD
    public function onPreUpdate(User $user): void
    {
        if ($user->getTelephone() && !$this->isEncrypted($user->getTelephone())) {
            $user->setTelephone($this->encryptionService->encrypt($user->getTelephone()));
        }
    }

    // Après avoir chargé l'utilisateur depuis la BDD (On remet en clair pour le site)
    public function onPostLoad(User $user): void
    {
        if ($user->getTelephone() && $this->isEncrypted($user->getTelephone())) {
            $decrypted = $this->encryptionService->decrypt($user->getTelephone());
            if ($decrypted) {
                $user->setTelephone($decrypted);
            }
        }
    }

    // Après un enregistrement, on remet le numéro en clair dans la mémoire de l'application
    // pour éviter qu'il s'affiche chiffré si la page se recharge directement
    public function onPostPersist(User $user): void
    {
        $this->onPostLoad($user); 
    }

    public function onPostUpdate(User $user): void
    {
        $this->onPostLoad($user);
    }

    /**
     * Petite sécurité pour éviter un double-chiffrement ou de tenter de déchiffrer
     * un ancien numéro de téléphone qui était stocké en clair (10 chiffres).
     */
    private function isEncrypted(string $data): bool
    {
        // Une donnée chiffrée par notre service fera toujours plus de 30 caractères
        return strlen($data) > 30;
    }
}