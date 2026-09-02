/**
 * Gestion dynamique de la grille tarifaire dans EasyAdmin
 * Génère automatiquement les champs de formules et tarifs personnalisés en fonction de minPersonnes et maxPersonnes.
 */
document.addEventListener('DOMContentLoaded', function() {
    initDynamicPricingGrid();
});

function initDynamicPricingGrid() {
    const minInput = document.querySelector('input[name$="[minPersonnes]"]');
    const maxInput = document.querySelector('input[name$="[maxPersonnes]"]');
    const jsonInput = document.querySelector('input[name$="[tarifsParPersonneJson]"]') || document.getElementById('tarifs-json-input');
    const prixBaseInput = document.querySelector('input[name$="[prix]"]') || document.getElementById('prix-base-input');

    if (!minInput || !maxInput) {
        return;
    }

    // Création du conteneur de la grille dynamique
    let container = document.getElementById('ea-dynamic-tarifs-card');
    if (!container) {
        container = document.createElement('div');
        container.id = 'ea-dynamic-tarifs-card';
        container.className = 'form-group field-tarifs-dynamiques mb-4';
        container.innerHTML = `
            <div style="background: #1a1a17; border: 1px solid #B89A63; border-left: 4px solid #B89A63; border-radius: 4px; padding: 20px; margin-top: 15px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <strong style="color: #B89A63; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                        💶 Personnalisation des Formules & Tarifs
                    </strong>
                    <span id="ea-tarifs-summary-badge" style="background: rgba(184, 154, 99, 0.2); color: #FFFFF0; border: 1px solid #B89A63; padding: 3px 10px; font-size: 12px; border-radius: 3px;">
                        Formules Actives
                    </span>
                </div>
                <p style="color: #D8D0BE; font-size: 13px; margin-bottom: 15px; line-height: 1.5;">
                    Personnalisez le titre (ex: <em>2 personnes</em> ou <em>3 personnes et +</em>), le sous-titre (ex: <em>Meilleur ami</em>, <em>Trouple</em>, <em>Couple</em>) et le tarif exact qui sera débité sur Stripe :
                </p>
                <div id="ea-dynamic-tarifs-fields" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">
                </div>
            </div>
        `;

        // Insertion après le champ maxPersonnes
        const maxFieldGroup = maxInput.closest('.form-group') || maxInput.closest('.field-integer') || maxInput.parentElement;
        if (maxFieldGroup && maxFieldGroup.parentNode) {
            maxFieldGroup.parentNode.insertBefore(container, maxFieldGroup.nextSibling);
        }
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

    function renderFields() {
        const min = parseInt(minInput.value, 10) || 1;
        let max = parseInt(maxInput.value, 10) || min;
        if (max < min) {
            max = min;
        }

        const fieldsContainer = document.getElementById('ea-dynamic-tarifs-fields');
        if (!fieldsContainer) return;

        fieldsContainer.innerHTML = '';

        for (let i = min; i <= max; i++) {
            const key = String(i);
            const rawData = tarifs[key] || {};
            
            // Extraction du prix, titre et sous-titre existants
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

            // Valeurs par défaut intelligentes
            if (!currentTitre) {
                if (i === max && i > 2) {
                    currentTitre = `${i} personnes et +`;
                } else if (i === 1) {
                    currentTitre = `1 personne`;
                } else {
                    currentTitre = `${i} personnes`;
                }
            }

            if (currentSousTitre === '') {
                if (i === 1) currentSousTitre = 'Individuel';
                else if (i === 2) currentSousTitre = 'Couple / Duo';
                else currentSousTitre = 'Groupe / Famille';
            }

            const fieldDiv = document.createElement('div');
            fieldDiv.style.background = '#252521';
            fieldDiv.style.border = '1px solid rgba(184, 154, 99, 0.4)';
            fieldDiv.style.borderRadius = '4px';
            fieldDiv.style.padding = '14px';

            fieldDiv.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid rgba(184,154,99,0.2); padding-bottom: 6px;">
                    <span style="font-size: 13px; font-weight: bold; color: #B89A63; text-transform: uppercase;">
                        👥 Palier ${i} (${i} pers.)
                    </span>
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 11px; color: #D8D0BE; margin-bottom: 3px; text-transform: uppercase;">
                        Titre affiché
                    </label>
                    <input type="text" 
                           class="form-control form-control-sm ea-tarif-titre-input" 
                           data-personnes="${i}" 
                           value="${currentTitre}" 
                           placeholder="ex: ${i} personnes, ${i} personnes et +" 
                           style="background: #111; border: 1px solid rgba(184, 154, 99, 0.5); color: #FFFFF0; font-size: 13px;">
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 11px; color: #D8D0BE; margin-bottom: 3px; text-transform: uppercase;">
                        Sous-titre / Relation
                    </label>
                    <input type="text" 
                           class="form-control form-control-sm ea-tarif-soustitre-input" 
                           data-personnes="${i}" 
                           value="${currentSousTitre}" 
                           placeholder="ex: Meilleur ami, Trouple, Famille..." 
                           style="background: #111; border: 1px solid rgba(184, 154, 99, 0.5); color: #FFFFF0; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; color: #D8D0BE; margin-bottom: 3px; text-transform: uppercase;">
                        Tarif Stripe (€)
                    </label>
                    <div style="display: flex; align-items: center;">
                        <input type="number" step="0.01" min="0" 
                               class="form-control ea-tarif-step-input" 
                               data-personnes="${i}" 
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
        const min = parseInt(minInput.value, 10) || 1;
        const max = parseInt(maxInput.value, 10) || min;
        const updatedTarifs = {};
        let firstPrice = null;

        for (let i = min; i <= max; i++) {
            const p = String(i);
            const priceInput = document.querySelector(`.ea-tarif-step-input[data-personnes="${p}"]`);
            const titreInput = document.querySelector(`.ea-tarif-titre-input[data-personnes="${p}"]`);
            const sousTitreInput = document.querySelector(`.ea-tarif-soustitre-input[data-personnes="${p}"]`);

            const priceVal = priceInput ? parseFloat(priceInput.value) : null;
            const titreVal = titreInput ? titreInput.value.trim() : '';
            const sousTitreVal = sousTitreInput ? sousTitreInput.value.trim() : '';

            if (priceVal !== null && !isNaN(priceVal) && priceVal >= 0) {
                updatedTarifs[p] = {
                    prix: priceVal,
                    titre: titreVal || `${p} personnes`,
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
    }

    // Écouteurs sur min et max
    minInput.addEventListener('input', renderFields);
    minInput.addEventListener('change', renderFields);
    maxInput.addEventListener('input', renderFields);
    maxInput.addEventListener('change', renderFields);

    // Initialisation
    renderFields();
}
