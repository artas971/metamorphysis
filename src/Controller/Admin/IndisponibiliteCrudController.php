<?php

namespace App\Controller\Admin;

use App\Entity\Indisponibilite;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class IndisponibiliteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Indisponibilite::class;
    }

    // Ajout d'un message d'alerte global en haut de la page
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Fermeture / Congé')
            ->setEntityLabelInPlural('Mes Congés & Fermetures')
            ->setHelp('index', '⚠️ <b>Attention :</b> Tout créneau ajouté ici bloquera automatiquement la prise de rendez-vous sur le site public pour cette période.');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre', 'Motif (ex: Vacances, Formation...)')
                ->setHelp('Ce motif est privé, vos clients verront seulement que le créneau est indisponible.'),
            
            DateTimeField::new('debut', 'Date et heure de début')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->renderAsNativeWidget()
                ->setHelp('Début du blocage de l\'agenda.'),
                
            DateTimeField::new('fin', 'Date et heure de fin')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->renderAsNativeWidget()
                ->setHelp('Fin du blocage. L\'agenda rouvrira automatiquement après cette heure.'),
        ];
    }
}