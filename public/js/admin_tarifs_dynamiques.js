/**
 * Gestion dynamique de la grille tarifaire dans EasyAdmin
 * Génère automatiquement les champs de formules et tarifs personnalisés en fonction du nombre de prix sélectionné.
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDynamicPricingGrid);
} else {
    initDynamicPricingGrid();
}

function initDynamicPricingGrid() {
    const nombrePrixSelect = document.querySelector('select[name$="[nombrePrix]"], input[name$="[nombrePrix]"], select[name*="nombrePrix"], #Prestation_nombrePrix');
    const minInput = document.querySelector('input[name$="[minPersonnes]"]') || document.getElementById('min-personnes-input');
    const maxInput = document.querySelector('input[name$="[maxPersonnes]"]') || document.getElementById('max-personnes-input');
    const jsonInput = document.querySelector('input[name$="[tarifsParPersonneJson]"]') || document.getElementById('tarifs-json-input');
    const prixBaseInput = document.querySelector('input[name$="[prix]"]') || document.getElementById('prix-base-input');

    if (!nombrePrixSelect && !minInput && !jsonInput) {
        return;
    }

    // Récupération des données existantes
    let tarifs = {};
    try {
        if (jsonInput && jsonInput.value && jsonInput.value.trim() !== '') {
            tarifs = JSON.parse(jsonInput.value);
        }
    } catch (e) {
        tarifs = {};
    }

    // Déterminer le nombre de prix initial
    let count = 1;
    if (nombrePrixSelect && nombrePrixSelect.value) {
        count = parseInt(nombrePrixSelect.value, 10) || 1;
    } else if (Object.keys(tarifs).length > 0) {
        count = Object.keys(tarifs).length;
        if (nombrePrixSelect) nombrePrixSelect.value = count;
    } else if (maxInput && maxInput.value) {
        count = parseInt(maxInput.value, 10) || 1;
        if (nombrePrixSelect) nombrePrixSelect.value = count;
    }

    // Création du conteneur de la grille dynamique
    let container = document.getElementById('ea-dynamic-tarifs-card');
    if (!container) {
        container = document.createElement('div');
        container.id = 'ea-dynamic-tarifs-card';
        container.className = 'col-12 form-group field-tarifs-dynamiques mb-4';
        container.innerHTML = `
            <div style="background: #1a1a17; border: 1px solid #B89A63; border-left: 4px solid #B89A63; border-radius: 4px; padding: 20px; margin-top: 15px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <strong style="color: #B89A63; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                        💶 Personnalisation des Prix & Formules
                    </strong>
                    <span id="ea-tarifs-summary-badge" style="background: rgba(184, 154, 99, 0.2); color: #FFFFF0; border: 1px solid #B89A63; padding: 3px 10px; font-size: 12px; border-radius: 3px;">
                        ${count === 1 ? '1 Tarif Unique' : count + ' Formules Actives'}
                    </span>
                </div>
                <p style="color: #D8D0BE; font-size: 13px; margin-bottom: 15px; line-height: 1.5;">
                    Renseignez pour chaque formule le titre (ex: <em>Individuel</em>, <em>Couple</em>, <em>Tarif standard</em>), le sous-titre optionnel et le montant exact en € débité sur Stripe :
                </p>
                <div id="ea-dynamic-tarifs-fields" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">
                </div>
            </div>
        `;

        // Insertion après le champ nombrePrix (ou maxFieldGroup en fallback)
        const targetAnchor = (nombrePrixSelect ? (nombrePrixSelect.closest('.field-choice') || nombrePrixSelect.closest('.form-group') || nombrePrixSelect.closest('[class*="field-"]') || nombrePrixSelect.closest('.col-12, .col-md-6, .col-sm-6') || nombrePrixSelect.parentElement) : null)
            || (maxInput ? (maxInput.closest('.form-group') || maxInput.closest('.field-integer') || maxInput.parentElement) : null);

        if (targetAnchor && targetAnchor.parentNode) {
            targetAnchor.parentNode.insertBefore(container, targetAnchor.nextSibling);
        } else {
            const form = document.querySelector('.ea-edit-form, .ea-new-form, form');
            if (form) {
                const row = form.querySelector('.row') || form;
                row.appendChild(container);
            }
        }
    }

    function renderFields() {
        const selectedCount = parseInt(nombrePrixSelect ? nombrePrixSelect.value : (maxInput ? maxInput.value : 1), 10) || 1;

        // Mise à jour de minInput et maxInput
        if (minInput) minInput.value = 1;
        if (maxInput) maxInput.value = selectedCount;

        const badge = document.getElementById('ea-tarifs-summary-badge');
        if (badge) {
            badge.textContent = selectedCount === 1 ? '1 Tarif Unique' : `${selectedCount} Formules Actives`;
        }

        const fieldsContainer = document.getElementById('ea-dynamic-tarifs-fields');
        if (!fieldsContainer) return;

        fieldsContainer.innerHTML = '';

        // Transformer les tarifs actuels en liste ordonnée pour conserver les valeurs même si les index diffèrent
        const existingEntries = Object.entries(tarifs);

        for (let i = 1; i <= selectedCount; i++) {
            const key = String(i);
            // Vérifier d'abord par clé exacte "1", "2"..., sinon par index positionnel
            let rawData = tarifs[key];
            if (!rawData && existingEntries[i - 1]) {
                rawData = existingEntries[i - 1][1];
            }
            rawData = rawData || {};

            let currentPrice = '';
            let currentTitre = '';
            let currentSousTitre = '';

            if (typeof rawData === 'object' && rawData !== null) {
                currentPrice = rawData.prix !== undefined ? rawData.prix : '';
                currentTitre = rawData.titre || '';
                currentSousTitre = rawData.sousTitre !== undefined ? rawData.sousTitre : '';
            } else if (typeof rawData === 'number' || typeof rawData === 'string') {
                currentPrice = rawData;
            }

            if (currentPrice === '' && i === 1 && prixBaseInput && prixBaseInput.value) {
                currentPrice = prixBaseInput.value;
            }

            // Valeurs par défaut intelligentes
            if (!currentTitre) {
                if (selectedCount === 1) {
                    currentTitre = '1 personne';
                } else if (i === 1) {
                    currentTitre = '1 personne';
                } else if (i === 2) {
                    currentTitre = '2 personnes';
                } else if (i === selectedCount) {
                    currentTitre = `${i} personnes et +`;
                } else {
                    currentTitre = `${i} personnes`;
                }
            }

            if (currentSousTitre === '') {
                if (selectedCount === 1) {
                    currentSousTitre = 'Individuel';
                } else if (i === 1) {
                    currentSousTitre = 'Individuel';
                } else if (i === 2) {
                    currentSousTitre = 'Couple / Duo';
                } else {
                    currentSousTitre = 'Groupe / Famille';
                }
            }

            const fieldDiv = document.createElement('div');
            fieldDiv.style.background = '#252521';
            fieldDiv.style.border = '1px solid rgba(184, 154, 99, 0.4)';
            fieldDiv.style.borderRadius = '4px';
            fieldDiv.style.padding = '14px';

            fieldDiv.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid rgba(184,154,99,0.2); padding-bottom: 6px;">
                    <span style="font-size: 13px; font-weight: bold; color: #B89A63; text-transform: uppercase;">
                        🏷️ Formule ${i} ${selectedCount === 1 ? '(Tarif unique)' : ''}
                    </span>
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 11px; color: #D8D0BE; margin-bottom: 3px; text-transform: uppercase;">
                        Titre de la formule
                    </label>
                    <input type="text" 
                           class="form-control form-control-sm ea-tarif-titre-input" 
                           data-formule="${i}" 
                           value="${currentTitre}" 
                           placeholder="ex: Individuel, Couple, Séance 1h..." 
                           style="background: #111; border: 1px solid rgba(184, 154, 99, 0.5); color: #FFFFF0; font-size: 13px;">
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 11px; color: #D8D0BE; margin-bottom: 3px; text-transform: uppercase;">
                        Sous-titre / Précision (optionnel)
                    </label>
                    <input type="text" 
                           class="form-control form-control-sm ea-tarif-soustitre-input" 
                           data-formule="${i}" 
                           value="${currentSousTitre}" 
                           placeholder="ex: Solo, Couple, Autre..." 
                           style="background: #111; border: 1px solid rgba(184, 154, 99, 0.5); color: #FFFFF0; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; color: #D8D0BE; margin-bottom: 3px; text-transform: uppercase;">
                        Tarif Stripe (€)
                    </label>
                    <div style="display: flex; align-items: center;">
                        <input type="number" step="0.01" min="0" 
                               class="form-control ea-tarif-step-input" 
                               data-formule="${i}" 
                               value="${currentPrice}" 
                               placeholder="0.00" 
                               style="background: #111; border: 1px solid #B89A63; color: #B89A63; font-weight: bold; font-size: 15px; border-radius: 3px 0 0 3px; height: 36px;">
                        <span style="background: #B89A63; color: #111; font-weight: bold; padding: 0 10px; height: 36px; display: flex; align-items: center; border-radius: 0 3px 3px 0; font-size: 13px;">
                            €
                        </span>
                    </div>
                </div>
            `;

            fieldsContainer.appendChild(fieldDiv);
        }

        // Écouteurs sur chaque champ généré
        fieldsContainer.querySelectorAll('.ea-tarif-step-input, .ea-tarif-titre-input, .ea-tarif-soustitre-input').forEach(input => {
            input.addEventListener('input', updateJsonAndBase);
            input.addEventListener('change', updateJsonAndBase);
        });

        updateJsonAndBase();
    }

    function updateJsonAndBase() {
        const selectedCount = parseInt(nombrePrixSelect ? nombrePrixSelect.value : (maxInput ? maxInput.value : 1), 10) || 1;
        const updatedTarifs = {};
        let firstPrice = null;

        for (let i = 1; i <= selectedCount; i++) {
            const p = String(i);
            const priceInput = document.querySelector(`.ea-tarif-step-input[data-formule="${p}"]`);
            const titreInput = document.querySelector(`.ea-tarif-titre-input[data-formule="${p}"]`);
            const sousTitreInput = document.querySelector(`.ea-tarif-soustitre-input[data-formule="${p}"]`);

            const priceVal = priceInput ? parseFloat(priceInput.value) : null;
            const titreVal = titreInput ? titreInput.value.trim() : '';
            const sousTitreVal = sousTitreInput ? sousTitreInput.value.trim() : '';

            if (priceVal !== null && !isNaN(priceVal) && priceVal >= 0) {
                updatedTarifs[p] = {
                    prix: priceVal,
                    titre: titreVal || (selectedCount === 1 ? 'Tarif unique' : `Formule ${p}`),
                    sousTitre: sousTitreVal
                };
                if (firstPrice === null) {
                    firstPrice = priceVal;
                }
            }
        }

        tarifs = updatedTarifs;

        if (jsonInput) {
            jsonInput.value = JSON.stringify(updatedTarifs);
        }

        if (prixBaseInput && firstPrice !== null) {
            prixBaseInput.value = firstPrice;
        }

        if (minInput) minInput.value = 1;
        if (maxInput) maxInput.value = selectedCount;
    }

    // Écouteurs sur nombrePrixSelect
    if (nombrePrixSelect) {
        nombrePrixSelect.addEventListener('change', renderFields);
        nombrePrixSelect.addEventListener('input', renderFields);

        // Si TomSelect est utilisé par EasyAdmin
        const hookTomSelect = () => {
            if (nombrePrixSelect && nombrePrixSelect.tomselect) {
                try {
                    nombrePrixSelect.tomselect.off('change', renderFields);
                } catch(e) {}
                nombrePrixSelect.tomselect.on('change', renderFields);
                return true;
            }
            return false;
        };

        if (!hookTomSelect()) {
            const tsInterval = setInterval(() => {
                if (hookTomSelect()) {
                    clearInterval(tsInterval);
                }
            }, 100);
            setTimeout(() => clearInterval(tsInterval), 3000);
        }
    }

    // Écouteur global en délégation pour capturer tout changement sur nombrePrix
    document.addEventListener('change', function(e) {
        if (e.target && (e.target.matches('select[name*="nombrePrix"]') || e.target.id === 'Prestation_nombrePrix')) {
            renderFields();
        }
    });

    // Clic sur option d'un dropdown TomSelect
    document.addEventListener('click', function(e) {
        const opt = e.target.closest('.ts-dropdown .option');
        if (opt) {
            setTimeout(renderFields, 60);
        }
    });

    // Sauvegarde avant soumission du formulaire
    const form = (nombrePrixSelect || jsonInput || minInput)?.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            updateJsonAndBase();
        });
    }

    // Initialisation
    renderFields();
}
