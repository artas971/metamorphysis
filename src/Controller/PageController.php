<?php

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PageController extends AbstractController
{ 
    // Gabarits codés en brut natifs par défaut (Fallback quand la page n'est pas publiée dans le Page Builder)
    private const NATIVE_PAGES = [
        'mentions-legales'              => 'pages/mentions_legales.html.twig',
        'a-propos'                      => 'pages/a_propos.html.twig',
        'lexperience'                   => 'pages/lexperience.html.twig',
        'a-qui-sadresse-cette-demarche' => 'pages/a_qui_sadresse_cette_demarche.html.twig',
    ];


    // LA ROUTE PUBLIQUE GÉNÉRIQUE
    #[Route('/{slug}', name: 'app_page_show', priority: -1)]
    public function show(string $slug, PageRepository $pageRepository): Response
    {
        // 1. On cherche la page dans le constructeur de pages (Base de données)
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        // 2. Si la case "Publié" est COCHÉE (En ligne = true) -> Rendu dynamique par le générateur de pages (Page Builder)
        if ($page && $page->isPublished()) {
            return $this->render('page/show.html.twig', [
                'page' => $page,
                'isDraftPreview' => false,
            ]);
        }

        // 3. Si la case est DÉSACTIVÉE / DÉCOCHÉE (ou absente en base) -> On bascule immédiatement sur le fichier statique brut par défaut
        if (isset(self::NATIVE_PAGES[$slug])) {
            return $this->render(self::NATIVE_PAGES[$slug]);
        }

        // 4. Sinon, erreur 404 standard
        throw $this->createNotFoundException('Cette page n\'est pas disponible ou est en cours de rédaction.');
    }

    // LA ROUTE D'APERÇU SÉCURISÉE
    #[Route('/admin/preview/{slug}', name: 'app_page_preview')]
    #[IsGranted('ROLE_ADMIN')] // Seul un admin peut entrer ici
    public function preview(string $slug, PageRepository $pageRepository): Response
    {
        // On cherche la page par son slug, PEU IMPORTE si elle est publiée ou non
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if (!$page) {
            throw $this->createNotFoundException('Cette page n\'existe pas.');
        }

        // On utilise le même template de rendu, mais on lui passe la variable 'isDraftPreview'
        return $this->render('page/show.html.twig', [
            'page' => $page,
            'isDraftPreview' => !$page->isPublished(), // Pour afficher le bandeau
        ]);
    }

    // NOUVELLE ROUTE : APERÇU TEMPORAIRE SANS AUCUNE SAUVEGARDE EN BASE
    #[Route('/admin/preview-draft/{id}', name: 'app_page_preview_draft', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function previewDraft(
        int $id,
        \Symfony\Component\HttpFoundation\Request $request,
        PageRepository $pageRepository,
        \Doctrine\ORM\EntityManagerInterface $em
    ): Response {
        $page = $pageRepository->find($id);
        if (!$page) {
            throw $this->createNotFoundException('Cette page n\'existe pas.');
        }

        // Détacher l'entité de l'EntityManager pour garantir 0 sauvegarde en base
        $em->detach($page);

        $data = $request->request->all();
        $pageData = $data['Page'] ?? $data;

        // Mise à jour éphémère de la Page en mémoire
        if (isset($pageData['titre'])) $page->setTitre($pageData['titre']);
        if (isset($pageData['slug'])) $page->setSlug($pageData['slug']);
        if (isset($pageData['metaDescription'])) $page->setMetaDescription($pageData['metaDescription']);
        if (isset($pageData['afficherTitre'])) $page->setAfficherTitre((bool)$pageData['afficherTitre']);
        if (isset($pageData['fondBlocsUnifie'])) $page->setFondBlocsUnifie($pageData['fondBlocsUnifie']);

        // Mise à jour éphémère des sections
        if (isset($pageData['sections']) && is_array($pageData['sections'])) {
            $existingSections = $page->getSections()->toArray();
            
            // Trier les sections existantes par ordre
            usort($existingSections, function ($a, $b) {
                return ($a->getOrdre() ?? 0) <=> ($b->getOrdre() ?? 0);
            });

            $updatedSections = new \Doctrine\Common\Collections\ArrayCollection();

            foreach ($pageData['sections'] as $index => $secData) {
                // Trouver la section existante correspondante par index
                $section = $existingSections[$index] ?? new \App\Entity\Section();
                $section->setPage($page);

                // Textes et Typographie
                $section->setTitre($secData['titre'] ?? $section->getTitre());
                $section->setTitreCouleur($secData['titreCouleur'] ?? $section->getTitreCouleur() ?? 'gold-hover');
                $section->setTitreLigneDecor($secData['titreLigneDecor'] ?? $section->getTitreLigneDecor() ?? 'apres');
                $section->setSousTitre($secData['sousTitre'] ?? $section->getSousTitre());
                $section->setSousTitreCouleur($secData['sousTitreCouleur'] ?? $section->getSousTitreCouleur() ?? 'ivory');
                $section->setContenu($secData['contenu'] ?? $section->getContenu());
                $section->setTexteCouleur($secData['texteCouleur'] ?? $section->getTexteCouleur() ?? 'ivory');
                $section->setDisposition($secData['disposition'] ?? $section->getDisposition());
                $section->setCouleurFond($secData['couleurFond'] ?? $section->getCouleurFond() ?? 'plum');
                $orderVal = isset($secData['ordre']) && $secData['ordre'] !== '' ? (int)$secData['ordre'] : (isset($secData['position']) && $secData['position'] !== '' ? (int)$secData['position'] : ($index + 1));
                $section->setOrdre($orderVal);
                $section->setTexteGras(!empty($secData['texteGras']));
                $section->setAlignementTexte($secData['alignementTexte'] ?? $section->getAlignementTexte() ?? 'center');
                $section->setBaliseHtml($secData['baliseHtml'] ?? 'h2');
                $section->setDecalagePosY(isset($secData['decalagePosY']) && $secData['decalagePosY'] !== '' ? (int)$secData['decalagePosY'] : 0);

                // Marges & Espacements (Padding / Margin)
                $section->setPaddingHaut(isset($secData['paddingHaut']) && $secData['paddingHaut'] !== '' ? (int)$secData['paddingHaut'] : null);
                $section->setPaddingBas(isset($secData['paddingBas']) && $secData['paddingBas'] !== '' ? (int)$secData['paddingBas'] : null);
                $section->setPaddingGauche(isset($secData['paddingGauche']) && $secData['paddingGauche'] !== '' ? (int)$secData['paddingGauche'] : null);
                $section->setPaddingDroite(isset($secData['paddingDroite']) && $secData['paddingDroite'] !== '' ? (int)$secData['paddingDroite'] : null);
                $section->setMargeHaut(isset($secData['margeHaut']) && $secData['margeHaut'] !== '' ? (int)$secData['margeHaut'] : null);
                $section->setMargeBas(isset($secData['margeBas']) && $secData['margeBas'] !== '' ? (int)$secData['margeBas'] : null);
                $section->setHauteurMin(isset($secData['hauteurMin']) && $secData['hauteurMin'] !== '' ? (int)$secData['hauteurMin'] : null);

                // Bordures & Cadre
                $section->setImageCadreCouleur($secData['imageCadreCouleur'] ?? $section->getImageCadreCouleur() ?? 'plum');
                $section->setImageCadreHaut(isset($secData['imageCadreHaut']) && $secData['imageCadreHaut'] !== '' ? (int)$secData['imageCadreHaut'] : 0);
                $section->setImageCadreBas(isset($secData['imageCadreBas']) && $secData['imageCadreBas'] !== '' ? (int)$secData['imageCadreBas'] : 0);
                $section->setImageCadreGauche(isset($secData['imageCadreGauche']) && $secData['imageCadreGauche'] !== '' ? (int)$secData['imageCadreGauche'] : 0);
                $section->setImageCadreDroite(isset($secData['imageCadreDroite']) && $secData['imageCadreDroite'] !== '' ? (int)$secData['imageCadreDroite'] : 0);

                // Positionnement & Superposition de l'image
                $section->setImagePosX(isset($secData['imagePosX']) && $secData['imagePosX'] !== '' ? (int)$secData['imagePosX'] : 0);
                $section->setImagePosY(isset($secData['imagePosY']) && $secData['imagePosY'] !== '' ? (int)$secData['imagePosY'] : 0);
                $section->setImageSuperposition($secData['imageSuperposition'] ?? $section->getImageSuperposition() ?? 'standard');
                $section->setImageZIndex(isset($secData['imageZIndex']) && $secData['imageZIndex'] !== '' ? (int)$secData['imageZIndex'] : 1);

                // Dimensions
                $section->setLargeurMedia(isset($secData['largeurMedia']) && $secData['largeurMedia'] !== '' ? (int)$secData['largeurMedia'] : null);
                $section->setHauteurMedia(isset($secData['hauteurMedia']) && $secData['hauteurMedia'] !== '' ? (int)$secData['hauteurMedia'] : null);
                $section->setMedia($secData['media'] ?? $section->getMedia());
                $section->setImageLien($secData['imageLien'] ?? $section->getImageLien());

                // Citations
                $section->setCitation($secData['citation'] ?? $section->getCitation());
                $section->setCitationCouleurFond($secData['citationCouleurFond'] ?? $section->getCitationCouleurFond() ?? 'meta-olive');
                $section->setCitationCouleurTexte($secData['citationCouleurTexte'] ?? $section->getCitationCouleurTexte() ?? 'meta-ivory');
                $section->setCitationPosX(isset($secData['citationPosX']) && $secData['citationPosX'] !== '' ? (int)$secData['citationPosX'] : -10);
                $section->setCitationPosY(isset($secData['citationPosY']) && $secData['citationPosY'] !== '' ? (int)$secData['citationPosY'] : -40);
                $section->setCitationLargeur(isset($secData['citationLargeur']) && $secData['citationLargeur'] !== '' ? (int)$secData['citationLargeur'] : 90);
                $section->setCitationHauteurMax(isset($secData['citationHauteurMax']) && $secData['citationHauteurMax'] !== '' ? (int)$secData['citationHauteurMax'] : null);

                // Boutons CTA
                $section->setBoutonTexte($secData['boutonTexte'] ?? $section->getBoutonTexte());
                $section->setBoutonLien($secData['boutonLien'] ?? $section->getBoutonLien());
                $section->setBoutonStyle($secData['boutonStyle'] ?? $section->getBoutonStyle() ?? 'gold');
                $section->setBoutonCible($secData['boutonCible'] ?? $section->getBoutonCible() ?? '_self');

                $updatedSections->add($section);
            }

            // Réassigner les sections triées par ordre
            $iterator = $updatedSections->getIterator();
            $iterator->uasort(function ($a, $b) {
                return ($a->getOrdre() ?? 0) <=> ($b->getOrdre() ?? 0);
            });
            $sortedSections = new \Doctrine\Common\Collections\ArrayCollection(iterator_to_array($iterator));

            $reflection = new \ReflectionClass($page);
            $prop = $reflection->getProperty('sections');
            $prop->setAccessible(true);
            $prop->setValue($page, $sortedSections);
        }

        return $this->render('page/show.html.twig', [
            'page' => $page,
            'isDraftPreview' => true,
        ]);
    }
}