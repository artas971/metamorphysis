<?php

namespace App\Controller\Admin;

use App\Entity\Seance;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SeanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Seance::class;
    }

    // 1. Configuration globale (Titre et Tri)
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Séance')
            ->setEntityLabelInPlural('Séances & Visioconférences')
            ->setPageTitle('index', 'Suivi des Séances') 
            ->setDefaultSort(['dateRendezVous' => 'DESC'])
            ->setHelp('index', 'Consultez et gérez les séances des parcours d\'accompagnement de vos clients. Vous pouvez valider les demandes de planification, suivre le statut des séances, lancer les visioconférences Daily.co et adapter le calendrier d\'activité en temps réel.');
    }

    // 2. Configuration des boutons (Actions)
    public function configureActions(Actions $actions): Actions
    {
        // Création d'une action personnalisée "Marquer comme effectuée"
        $marquerEffectuee = Action::new('marquerEffectuee', 'Effectuée', 'fas fa-check-circle')
            ->linkToCrudAction('changerStatutEffectuee')
            ->addCssClass('btn btn-sm btn-success text-white')
            ->displayIf(static function ($entity) {
                // On affiche le bouton seulement si la séance a une date et n'est pas déjà annulée ou effectuée
                return $entity->getDateRendezVous() !== null && $entity->getStatut() !== 'Effectuée';
            });

        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $marquerEffectuee);
    }

    // Mémoire de l'action personnalisée
    #[AdminRoute]
    public function changerStatutEffectuee(AdminContext $context, EntityManagerInterface $entityManager): RedirectResponse
    {
        /** @var Seance $seance */
        $seance = $context->getEntity()->getInstance();
        
        $seance->setStatut('Effectuée');
        
        $entityManager->flush();

        $numero = $seance->getNumero();
        $this->addFlash('success', 'La séance n°' . ($numero !== null ? $numero : 'inconnue') . ' a été marquée comme effectuée.');

        $referer = $context->getRequest()->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('admin'));
    }

    // 3. Configuration des champs affichés
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            AssociationField::new('user', 'Client')
                ->setDisabled(true),

            TextField::new('user.telephone', 'Téléphone Client')
                ->formatValue(function ($value, $entity) {
                    return $entity->getUser() ? $entity->getUser()->getTelephone() : 'Non renseigné';
                })
                ->onlyOnIndex(),

            AssociationField::new('prestation', 'Soin / Parcours')
                ->setDisabled(true),

            IntegerField::new('numero', 'Séance N°')
                ->setDisabled(true)
                ->setHelp('Position chronologique de cette séance dans le parcours de l\'accompagnement.'),

            DateTimeField::new('dateRendezVous', 'Date et Heure du RDV')
                ->setFormat('dd/MM/yyyy HH:mm'),

            ChoiceField::new('statut', 'Statut de la demande')
                ->setChoices([
                    'Non planifiée'           => 'Non planifiée',
                    'En attente de validation' => 'En attente de validation',
                    'Confirmé'                => 'Confirmé',
                    'Effectuée'               => 'Effectuée',
                    'Annulé'                  => 'Annulé',
                ])
                ->renderAsBadges([
                    'Non planifiée'           => 'secondary',
                    'En attente de validation' => 'warning',
                    'Confirmé'                => 'success',
                    'Effectuée'               => 'info',
                    'Annulé'                  => 'danger',
                ]),

            UrlField::new('lienVisio', 'Visioconférence')
                ->setTemplatePath('admin/fields/lien_visio.html.twig')
                ->setHelp('Lien unique généré automatiquement pour rejoindre la salle de soin en ligne.'),
        ];
    }
}