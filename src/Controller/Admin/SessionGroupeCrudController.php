<?php

namespace App\Controller\Admin;

use App\Entity\SessionGroupe;
use App\Service\DailyCoService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class SessionGroupeCrudController extends AbstractCrudController
{
    public function __construct(
        private DailyCoService $dailyCoService
    ) {}

    public static function getEntityFqcn(): string
    {
        return SessionGroupe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("Séance d'Accompagnement en Groupe")
            ->setEntityLabelInPlural("Séances d'Accompagnement en Groupe")
            ->setDefaultSort(['dateDebut' => 'DESC'])
            ->setHelp('index', 'Consultez et gérez vos séances d\'accompagnement en groupe. Vous pouvez valider une séance pour déclencher les débits et activer la visioconférence, ou annuler sans aucun débit pour les participants.');
    }

    public function configureActions(Actions $actions): Actions
    {
        $planifierSuivante = Action::new('planifierSuivante', 'Planifier séance suivante', 'fa fa-calendar-plus text-warning')
            ->linkToRoute('admin_session_planifier_suivante', function (SessionGroupe $session) {
                return ['id' => $session->getId()];
            })
            ->displayIf(fn (SessionGroupe $s) => $s->getGroupe() !== null);

        $validerDebiter = Action::new('validerDebiter', 'Valider & Débiter (30 €)', 'fa fa-check-double text-success')
            ->linkToRoute('admin_session_valider_debiter', function (SessionGroupe $session) {
                return ['id' => $session->getId()];
            })
            ->displayIf(fn (SessionGroupe $s) => $s->getStatut() === "En cours d'inscriptions");

        $annulerLiberer = Action::new('annulerLiberer', 'Annuler & Libérer les empreintes', 'fa fa-ban text-danger')
            ->linkToRoute('admin_session_annuler_liberer', function (SessionGroupe $session) {
                return ['id' => $session->getId()];
            })
            ->displayIf(fn (SessionGroupe $s) => !in_array($s->getStatut(), ['Annulé', 'Effectué']));

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $planifierSuivante)
            ->add(Crud::PAGE_INDEX, $validerDebiter)
            ->add(Crud::PAGE_INDEX, $annulerLiberer)
            ->add(Crud::PAGE_DETAIL, $planifierSuivante)
            ->add(Crud::PAGE_DETAIL, $validerDebiter)
            ->add(Crud::PAGE_DETAIL, $annulerLiberer);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', '#')->hideOnForm();

        yield AssociationField::new('groupe', '🏷️ Groupe de Suivi')
            ->setHelp('Groupe de cohorte auquel cette séance est rattachée (optionnel).');

        yield IntegerField::new('numeroSeance', 'N° Séance')
            ->setHelp('Indiquez 1 pour la 1ère séance (pouvant être publique sur le site). Indiquez 2, 3... pour les séances suivantes de suivi (strictement privées pour la cohorte).')
            ->formatValue(function ($value) {
                if ($value === null || $value <= 1) {
                    return '<span class="badge" style="background:#28a745;color:#fff;padding:5px 8px;font-size:12px;"><i class="fa fa-door-open me-1"></i>Séance 1 (Initiale / Publique si activée)</span>';
                }
                return sprintf('<span class="badge" style="background:#17a2b8;color:#fff;padding:5px 8px;font-size:12px;"><i class="fa fa-users me-1"></i>Séance %d (Privée cohorte)</span>', $value);
            });

        yield TextField::new('titre', 'Thème / Titre')
            ->setHelp('Ex: Approfondissement et libération émotionnelle');

        yield AssociationField::new('prestation', '💆 Prestation')
            ->setRequired(true);

        yield BooleanField::new('estDateADefinir', '🗓️ Date à définir ultérieurement')
            ->setHelp('Activez cette option si vous souhaitez promouvoir cette session sans avoir encore fixé la date exacte. Les réservations par carte bancaire seront verrouillées jusqu\'à la fixation de la date.')
            ->renderAsSwitch(true);

        yield DateTimeField::new('dateDebut', '📅 Date & Heure')
            ->setRequired(false)
            ->setHelp('Laissez vide si la date est encore à définir ultérieurement.')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->formatValue(function ($value, SessionGroupe $entity) {
                if ($entity->isEstDateADefinir() || !$entity->getDateDebut()) {
                    return '<span class="badge" style="background:#6c757d;color:#fff;padding:5px 8px;font-size:12px;"><i class="fa fa-clock me-1"></i>Date à définir ultérieurement</span>';
                }
                return $entity->getDateDebut()->format('d/m/Y H:i');
            });

        yield DateTimeField::new('dateFin', '⏰ Fin')
            ->setRequired(false)
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnIndex();

        yield ChoiceField::new('statut', 'Statut de la séance')
            ->setChoices([
                "En cours d'inscriptions" => "En cours d'inscriptions",
                'Confirmé (Validé et Débité)' => 'Confirmé',
                'Annulé (Empreintes libérées)' => 'Annulé',
                'Effectué' => 'Effectué',
            ])
            ->renderAsBadges([
                "En cours d'inscriptions" => 'warning',
                'Confirmé' => 'success',
                'Annulé' => 'danger',
                'Effectué' => 'secondary',
            ]);

        yield BooleanField::new('estVisiblePublic', '👁️ Visible sur le site public')
            ->setHelp('⚠️ Uniquement pour la Séance n°1 ! Si activé : les visiteurs du site public peuvent réserver cette 1ère séance. Pour toute séance 2 ou +, cette option est automatiquement désactivée car la séance est réservée uniquement à votre groupe de suivi.')
            ->renderAsSwitch(true);

        yield TextField::new('jaugeInscriptions', '👥 Présences & Inscrits')
            ->onlyOnIndex()
            ->formatValue(function ($value, SessionGroupe $entity) {
                return sprintf('<strong>%d confirmés</strong> / %d min <span class="text-muted">(%d en attente, %d déclinés)</span>', 
                    $entity->getNombreConfirmes(), 
                    $entity->getSeuilMinimum(),
                    $entity->getNombreEnAttente(),
                    $entity->getNombreDeclines()
                );
            });

        yield UrlField::new('lienVisio', '📹 Lien Salle Visio (Daily.co)')
            ->setHelp('Généré automatiquement dès confirmation de la séance.');

        yield TextareaField::new('notesTherapeute', '📝 Prise de notes & Suivi de séance')
            ->setHelp('Notes personnelles et confidentielles (progression du groupe, points abordés...)')
            ->hideOnIndex();

        yield AssociationField::new('inscriptions', 'Participants & Réponses')
            ->onlyOnDetail()
            ->formatValue(function ($value, SessionGroupe $entity) {
                $lines = [];
                foreach ($entity->getInscriptions() as $i) {
                    $presenceBadge = match($i->getStatutPresence()) {
                        'Confirmé' => '<span class="badge badge-success" style="background:#28a745;">Confirmé</span>',
                        'Décliné' => '<span class="badge badge-danger" style="background:#dc3545;">Décliné</span>',
                        default => '<span class="badge badge-warning" style="background:#ffc107;color:#000;">En attente</span>',
                    };
                    $paiementBadge = match($i->getStatutPaiement()) {
                        'Payé' => '<span class="badge badge-success" style="background:#28a745;">Payé (30 €)</span>',
                        'Empreinte validée' => '<span class="badge badge-info" style="background:#17a2b8;">Empreinte validée</span>',
                        'Annulé' => '<span class="badge badge-secondary" style="background:#6c757d;">Annulé (0 €)</span>',
                        default => '<span class="badge badge-light" style="background:#e9ecef;color:#000;">En attente</span>',
                    };
                    $msg = $i->getMessageParticipant() ? '<br><em>💬 Message : "' . htmlspecialchars($i->getMessageParticipant()) . '"</em>' : '';
                    $lines[] = sprintf('&bull; <strong>%s</strong> (%s) — %s %s%s', 
                        htmlspecialchars($i->getNomComplet()), 
                        htmlspecialchars($i->getEmail()),
                        $presenceBadge,
                        $paiementBadge,
                        $msg
                    );
                }
                return implode('<br><br>', $lines) ?: 'Aucun participant pour le moment.';
            });
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof SessionGroupe) {
            // Sécurité : Les séances de suivi (n° 2, 3...) sont strictement privées pour la cohorte
            if ($entityInstance->getNumeroSeance() > 1) {
                $entityInstance->setEstVisiblePublic(false);
            }
            // Si aucune date n'est saisie, la séance est automatiquement marquée comme "Date à définir ultérieurement"
            if ($entityInstance->getDateDebut() === null) {
                $entityInstance->setEstDateADefinir(true);
            }
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof SessionGroupe) {
            // Sécurité : Les séances de suivi (n° 2, 3...) sont strictement privées pour la cohorte
            if ($entityInstance->getNumeroSeance() > 1) {
                $entityInstance->setEstVisiblePublic(false);
            }
            // Si aucune date n'est saisie, la séance est automatiquement marquée comme "Date à définir ultérieurement"
            if ($entityInstance->getDateDebut() === null) {
                $entityInstance->setEstDateADefinir(true);
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}

