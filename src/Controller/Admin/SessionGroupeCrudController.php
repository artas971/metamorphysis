<?php

namespace App\Controller\Admin;

use App\Entity\SessionGroupe;
use App\Service\DailyCoService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
            ->setHelp('Numéro d\'ordre dans le parcours de groupe (ex: 1, 2, 3...)');

        yield TextField::new('titre', 'Thème / Titre')
            ->setHelp('Ex: Approfondissement et libération émotionnelle');

        yield AssociationField::new('prestation', '💆 Prestation')
            ->setRequired(true);

        yield DateTimeField::new('dateDebut', '📅 Date & Heure')
            ->setRequired(true)
            ->setFormat('dd/MM/yyyy HH:mm');

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

        yield TextareaField::new('notesTherapeute', 'Notes privées de la praticienne')
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
}
