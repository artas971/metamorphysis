document.addEventListener('DOMContentLoaded', function() {
    // Utilitaire Debounce pour la performance de saisie
    function debounce(func, wait = 75) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ======================================================
    // 1. GESTION DE L'APERÇU EN DIRECT (SPLIT-SCREEN PREVIEW)
    // ======================================================
    const previewIframe = document.getElementById('builderPreviewIframe');
    const previewArea = document.getElementById('builderPreviewArea');
    const toggleDrawerBtn = document.getElementById('builderToggleDrawerBtn');
    const refreshBtn = document.getElementById('builderRefreshBtn');
    const deviceBtns = document.querySelectorAll('.builder-device-btn');

    if (previewIframe) {
        let currentMode = 'desktop';

        function applyResponsiveMode() {
            const container = document.querySelector('.builder-preview-body');
            if (!container || container.clientWidth === 0) return;
            const containerWidth = container.clientWidth;
            const containerHeight = container.clientHeight || (window.innerHeight - 150);

            previewIframe.className = 'builder-preview-iframe mode-' + currentMode;

            if (currentMode === 'desktop') {
                const targetWidth = 1440; // Résolution PC Desktop standard
                const scale = containerWidth / targetWidth;
                previewIframe.style.width = targetWidth + 'px';
                previewIframe.style.height = (containerHeight / scale) + 'px';
                previewIframe.style.transform = 'scale(' + scale + ')';
                previewIframe.style.transformOrigin = '0 0';
                previewIframe.style.left = '0';
                previewIframe.style.top = '0';
            } else if (currentMode === 'tablet') {
                const targetWidth = 768; // Résolution Tablette iPad standard
                const scale = Math.min(1, (containerWidth - 20) / targetWidth);
                previewIframe.style.width = targetWidth + 'px';
                previewIframe.style.height = ((containerHeight - 30) / scale) + 'px';
                previewIframe.style.transform = 'translate(-50%, -50%) scale(' + scale + ')';
                previewIframe.style.transformOrigin = 'center center';
                previewIframe.style.left = '50%';
                previewIframe.style.top = '50%';
            } else if (currentMode === 'mobile') {
                const targetWidth = 375; // Résolution Smartphone standard
                const scale = Math.min(1, (containerWidth - 20) / targetWidth);
                previewIframe.style.width = targetWidth + 'px';
                previewIframe.style.height = ((containerHeight - 30) / scale) + 'px';
                previewIframe.style.transform = 'translate(-50%, -50%) scale(' + scale + ')';
                previewIframe.style.transformOrigin = 'center center';
                previewIframe.style.left = '50%';
                previewIframe.style.top = '50%';
            }
        }

        // Initialisation & écoute du redimensionnement de fenêtre
        setTimeout(applyResponsiveMode, 100);
        window.addEventListener('resize', applyResponsiveMode);

        // Mode responsive (Desktop / Tablette / Mobile)
        deviceBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                deviceBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                currentMode = this.getAttribute('data-device');
                applyResponsiveMode();
            });
        });

        // Bouton Rafraîchir : Aperçu en Direct Instantané (SANS enregistrer en base)
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                const draftUrl = refreshBtn.getAttribute('data-preview-draft-url');
                const form = document.querySelector('form.ea-edit-form, form.ea-new-form, form');

                if (draftUrl && form) {
                    const originalHtml = refreshBtn.innerHTML;
                    refreshBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                    
                    const formData = new FormData(form);

                    fetch(draftUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Erreur de génération de l\'aperçu');
                        return response.text();
                    })
                    .then(html => {
                        previewIframe.srcdoc = html;
                    })
                    .catch(err => {
                        console.error('Erreur d\'aperçu en direct:', err);
                        previewIframe.src = previewIframe.src;
                    })
                    .finally(() => {
                        refreshBtn.innerHTML = originalHtml;
                    });
                } else {
                    previewIframe.src = previewIframe.src;
                }
            });
        }

        // Bouton Capture d'Écran Haute Définition (Export PNG pour le client)
        const screenshotBtn = document.getElementById('builderScreenshotBtn');
        if (screenshotBtn) {
            screenshotBtn.addEventListener('click', async function() {
                if (!previewIframe || !previewIframe.contentWindow) {
                    alert('Veuillez d\'abord générer l\'aperçu de la page pour effectuer une capture.');
                    return;
                }

                const originalHtml = screenshotBtn.innerHTML;
                screenshotBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                screenshotBtn.disabled = true;

                try {
                    // Charger dynamiquement html-to-image (beaucoup plus moderne et sans bug de Range)
                    if (typeof htmlToImage === 'undefined') {
                        await new Promise((resolve, reject) => {
                            const script = document.createElement('script');
                            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js';
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                    }

                    const iframeDoc = previewIframe.contentDocument || previewIframe.contentWindow.document;
                    const targetEl = iframeDoc.body || iframeDoc.documentElement;

                    // Capture haute définition Retina 2x avec html-to-image
                    const dataUrl = await htmlToImage.toPng(targetEl, {
                        quality: 1.0,
                        pixelRatio: 2,
                        backgroundColor: '#1E1617',
                        cacheBust: true,
                        style: {
                            transform: 'none',
                            transformOrigin: 'top left'
                        }
                    });

                    // Téléchargement automatique du fichier PNG
                    const link = document.createElement('a');
                    const pageTitle = document.querySelector('input[name*="[titre]"]')?.value?.trim() || 'Maquette_Metamorphysis';
                    const cleanName = pageTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase();
                    const now = new Date();
                    const dateStr = now.toISOString().slice(0, 10);
                    
                    link.download = `Maquette_${cleanName}_${dateStr}.png`;
                    link.href = dataUrl;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                } catch (err) {
                    console.error('Erreur de capture :', err);
                    alert('Erreur lors de la capture d\'écran : ' + err.message);
                } finally {
                    screenshotBtn.innerHTML = originalHtml;
                    screenshotBtn.disabled = false;
                }
            });
        }

        // Raccourci Clavier Ctrl + S ou Cmd + S pour Sauvegarder Définitivement en Base
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const saveAndContinueBtn = document.querySelector('button[value="saveAndContinue"], .action-saveAndContinue');
                if (saveAndContinueBtn) {
                    saveAndContinueBtn.click();
                }
            }
        });

        // Tiroir pour petits écrans
        if (toggleDrawerBtn && previewArea) {
            toggleDrawerBtn.addEventListener('click', function() {
                previewArea.classList.toggle('is-open');
                if (previewArea.classList.contains('is-open')) {
                    toggleDrawerBtn.innerHTML = '<i class="fa fa-times"></i> Fermer l\'aperçu';
                } else {
                    toggleDrawerBtn.innerHTML = '<i class="fa fa-eye"></i> Aperçu en direct';
                }
            });
        }
    }

    // ======================================================
    // 2. MASQUAGE / AFFICHAGE DYNAMIQUE DES CHAMPS PAR BLOC
    // ======================================================
    function findFieldsetContainer(context, targetClass) {
        const marker = context.querySelector('.' + targetClass);
        if (!marker) return null;
        return marker.closest('.form-fieldset, fieldset, .card, .accordion-item') || marker;
    }

    function toggleField(sectionEl, fieldName, visible) {
        // 1. Recherche par classe EasyAdmin
        const directEl = sectionEl.querySelector('.builder-field-' + fieldName);
        if (directEl) {
            directEl.style.display = visible ? '' : 'none';
            return;
        }

        // 2. Recherche par attribut de nom d'input/select/textarea
        const input = sectionEl.querySelector(`[name*="[${fieldName}]"]`);
        if (input) {
            const group = input.closest('.field-text, .field-choice, .field-textarea, .field-boolean, .field-integer, .form-group, .field-group, .col-12, .col-md-6, .col-md-4') || input.parentElement;
            if (group) group.style.display = visible ? '' : 'none';
        }
    }

    function updateSectionVisibility(sectionEl) {
        if (!sectionEl) return;

        const select = sectionEl.querySelector('select[name*="[disposition]"]');
        if (!select) return;

        const disp = select.value;

        const fieldsetTexte = findFieldsetContainer(sectionEl, 'builder-fieldset-texte');
        const fieldsetImage = findFieldsetContainer(sectionEl, 'builder-fieldset-image');
        const fieldsetImageAdv = findFieldsetContainer(sectionEl, 'builder-fieldset-image-advanced');
        const fieldsetCitation = findFieldsetContainer(sectionEl, 'builder-fieldset-citation');
        const fieldsetColonnes = findFieldsetContainer(sectionEl, 'builder-fieldset-colonnes');
        const fieldsetPrestations = findFieldsetContainer(sectionEl, 'builder-fieldset-prestations');
        const fieldsetCta = findFieldsetContainer(sectionEl, 'builder-fieldset-cta');
        const fieldsetEspacement = findFieldsetContainer(sectionEl, 'builder-fieldset-espacement');

        const isImage = ['img_gauche', 'img_droite', 'img_centre', 'banniere', 'presentation_expert'].includes(disp);
        const isColonnes = (disp === 'grille_colonnes');
        const isFlexRow = (disp === 'flex_row');
        const isPrestations = (disp === 'slider_prestations');
        const isBandeau = (disp === 'bandeau_conclusion');
        const isTexteCentre = (disp === 'texte_centre');

        // Visibilité des Fieldsets
        if (fieldsetTexte) fieldsetTexte.style.display = '';
        if (fieldsetImage) fieldsetImage.style.display = isImage ? '' : 'none';
        if (fieldsetImageAdv) fieldsetImageAdv.style.display = isImage ? '' : 'none';
        if (fieldsetCitation) fieldsetCitation.style.display = (isImage || isPrestations) ? '' : 'none';
        if (fieldsetColonnes) fieldsetColonnes.style.display = (isColonnes || isFlexRow) ? '' : 'none';
        if (fieldsetPrestations) fieldsetPrestations.style.display = isPrestations ? '' : 'none';
        if (fieldsetCta) fieldsetCta.style.display = (isTexteCentre || isImage || isPrestations || isFlexRow) ? '' : 'none';
        if (fieldsetEspacement) fieldsetEspacement.style.display = '';

        // Visibilité granulaire des champs de texte
        if (isColonnes || isFlexRow) {
            toggleField(sectionEl, 'titre', true);
            toggleField(sectionEl, 'titreCouleur', true);
            toggleField(sectionEl, 'titreLigneDecor', true);
            toggleField(sectionEl, 'sousTitre', true);
            toggleField(sectionEl, 'sousTitreCouleur', true);
            toggleField(sectionEl, 'baliseHtml', false);
            toggleField(sectionEl, 'contenu', true); // Intro de section optionnelle
            toggleField(sectionEl, 'texteCouleur', true); // Couleur des textes de colonnes
            toggleField(sectionEl, 'texteGras', true);
        } else if (isBandeau) {
            toggleField(sectionEl, 'titre', true);
            toggleField(sectionEl, 'titreCouleur', true);
            toggleField(sectionEl, 'titreLigneDecor', true);
            toggleField(sectionEl, 'sousTitre', true);
            toggleField(sectionEl, 'sousTitreCouleur', true);
            toggleField(sectionEl, 'baliseHtml', false);
            toggleField(sectionEl, 'contenu', true);
            toggleField(sectionEl, 'texteCouleur', true);
            toggleField(sectionEl, 'texteGras', false);
        } else if (isPrestations) {
            toggleField(sectionEl, 'titre', true);
            toggleField(sectionEl, 'titreCouleur', true);
            toggleField(sectionEl, 'titreLigneDecor', true);
            toggleField(sectionEl, 'sousTitre', true);
            toggleField(sectionEl, 'sousTitreCouleur', true);
            toggleField(sectionEl, 'baliseHtml', false);
            toggleField(sectionEl, 'contenu', true); // Utilisé pour la mention légale en bas
            toggleField(sectionEl, 'texteCouleur', false);
            toggleField(sectionEl, 'texteGras', false);
        } else {
            toggleField(sectionEl, 'titre', true);
            toggleField(sectionEl, 'titreCouleur', true);
            toggleField(sectionEl, 'titreLigneDecor', true);
            toggleField(sectionEl, 'sousTitre', true);
            toggleField(sectionEl, 'sousTitreCouleur', true);
            toggleField(sectionEl, 'baliseHtml', true);
            toggleField(sectionEl, 'contenu', true);
            toggleField(sectionEl, 'texteCouleur', true);
            toggleField(sectionEl, 'texteGras', true);
        }
    }

    // ======================================================
    // 3. EN-TÊTE DYNAMIQUE DES BLOCS ACCORDÉON
    // ======================================================
    const DISP_LABELS = {
        'texte_centre': '📄 Texte Centré',
        'img_gauche': '🖼️ Image à Gauche + Texte',
        'img_droite': '🖼️ Texte à Gauche + Image',
        'img_centre': '🖼️ Image au Centre + Textes',
        'presentation_expert': '👑 Présentation Expert (Louisa Chouihi)',
        'grille_colonnes': '📊 Grille Multi-Colonnes (2 à 5 colonnes)',
        'flex_row': '📦 Rangée Flexible / Conteneur Horizontal',
        'grille_mentions': '⚖️ Grille Mentions Légales & Juridique',
        'banniere': '🌅 Bannière Pleine Largeur',
        'slider_prestations': '🎠 Carrousel des Prestations',
        'bandeau_conclusion': '🌸 Bandeau Signature & Logo M',
        'info_pratique': '🌸 Bloc Info Pratique'
    };

    function updateAccordionHeader(itemEl) {
        if (!itemEl) return;

        const select = itemEl.querySelector('select[name*="[disposition]"]');
        const isMainSection = !!select;

        const button = itemEl.querySelector('button.accordion-button');
        if (!button) return;

        let fullText = '';
        if (isMainSection) {
            // Bloc principal (Section)
            const ordreInput = itemEl.querySelector('input[name*="[ordre]"], input[name*="[position]"]');
            const titreInput = itemEl.querySelector('input[name*="[titre]"]');

            const disp = select ? select.value : 'texte_centre';
            const label = DISP_LABELS[disp] || 'Bloc';
            const orderVal = ordreInput && ordreInput.value !== '' ? ordreInput.value : '0';
            const titreVal = titreInput && titreInput.value.trim() ? ' : ' + titreInput.value.trim() : '';

            fullText = `${label} (Position ${orderVal})${titreVal}`;
        } else {
            // Sous-élément (Colonne du bloc Multi-Colonnes)
            const titreInput = itemEl.querySelector('input[name*="[titre]"]');
            const titreVal = titreInput && titreInput.value.trim() ? ' : ' + titreInput.value.trim() : '';

            let colIndex = 1;
            if (itemEl.parentElement) {
                const siblings = [...itemEl.parentElement.children].filter(el => el.classList.contains('field-collection-item'));
                const pos = siblings.indexOf(itemEl);
                if (pos !== -1) colIndex = pos + 1;
            }

            fullText = `🏛️ Colonne ${colIndex}${titreVal}`;
        }

        // On préserve l'icône de pliage EasyAdmin (.form-collection-item-collapse-marker / svg / i)
        const marker = button.querySelector('.form-collection-item-collapse-marker, svg, i');
        let titleSpan = button.querySelector('.builder-accordion-title');

        if (!titleSpan) {
            titleSpan = document.createElement('span');
            titleSpan.className = 'builder-accordion-title ms-2';
            titleSpan.style.cssText = 'white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 600; display: inline-block; max-width: 90%;';
            
            button.textContent = '';
            if (marker) button.appendChild(marker);
            button.appendChild(titleSpan);
        }

        titleSpan.textContent = fullText;
    }

    function autoIncrementNewBlocks() {
        const sectionsCollection = document.querySelector('[class*="field-collection"]');
        if (!sectionsCollection) return;

        // 1. Calculer la position maximale existante
        let maxPos = 0;
        const mainSections = [];
        sectionsCollection.querySelectorAll('.field-collection-item').forEach(item => {
            if (item.querySelector('select[name*="[disposition]"]')) {
                mainSections.push(item);
                const input = item.querySelector('input[name*="[ordre]"], input[name*="[position]"]');
                if (input && input.value !== '' && !isNaN(parseInt(input.value))) {
                    maxPos = Math.max(maxPos, parseInt(input.value));
                }
            }
        });

        // 2. Assigner uniquement aux nouveaux blocs non initialisés
        mainSections.forEach((item, index) => {
            const input = item.querySelector('input[name*="[ordre]"], input[name*="[position]"]');
            if (input && input.value === '' && !item.hasAttribute('data-initialized-order')) {
                input.value = maxPos + 1;
                maxPos++;
                item.setAttribute('data-initialized-order', 'true');
            }
            updateAccordionHeader(item);
        });
    }

    function initAllSections() {
        // Formulaires directs de Section ou éléments de collection dans Page
        const forms = document.querySelectorAll('form, .field-collection-item, [class*="field-collection"]');
        forms.forEach(container => {
            if (container.querySelector('select[name*="[disposition]"]')) {
                updateSectionVisibility(container);
            }
        });
        autoIncrementNewBlocks();
    }

    // Écoute des changements utilisateur en temps réel
    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches('select[name*="[disposition]"], input[name*="[ordre]"], input[name*="[position]"], input[name*="[titre]"]')) {
            const container = e.target.closest('.field-collection-item, form, [class*="field-collection"]') || document;
            updateSectionVisibility(container);
            updateAccordionHeader(container);
        }
    });

    const debouncedUpdateAccordionHeader = debounce(function(container) {
        updateAccordionHeader(container);
    }, 75);

    document.addEventListener('input', function(e) {
        if (e.target && e.target.matches('input[name*="[ordre]"], input[name*="[position]"], input[name*="[titre]"]')) {
            const container = e.target.closest('.field-collection-item, form, [class*="field-collection"]') || document;
            debouncedUpdateAccordionHeader(container);
        }
    });

    // Initialisation au chargement
    initAllSections();

    // Surveillance ciblée des ajouts d'éléments (clic sur "Ajouter un élément")
    document.addEventListener('click', function(e) {
        if (e.target && e.target.closest('.field-collection-add-button, [class*="collection-add"], [data-ea-collection-add]')) {
            setTimeout(function() {
                initAllSections();
            }, 150);
        }
    });
});

