/**
 * Gestion dynamique de la grille tarifaire dans EasyAdmin
 * Génère automatiquement les champs de tarifs en fonction de minPersonnes et maxPersonnes.
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
                        💶 Tarifs Fixés par Nombre de Participants
                    </strong>
                    <span id="ea-tarifs-summary-badge" style="background: rgba(184, 154, 99, 0.2); color: #FFFFF0; border: 1px solid #B89A63; padding: 3px 10px; font-size: 12px; border-radius: 3px;">
                        Formules Actives
                    </span>
                </div>
                <p style="color: #D8D0BE; font-size: 13px; margin-bottom: 15px; line-height: 1.5;">
                    Indiquez le montant exact qui sera débité sur Stripe en fonction du nombre de personnes choisi par le client sur le site :
                </p>
                <div id="ea-dynamic-tarifs-fields" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px;">
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
            const currentVal = tarifs[key] !== undefined ? tarifs[key] : (prixBaseInput && prixBaseInput.value ? prixBaseInput.value : '');

            let labelText = `Tarif pour ${i} personne`;
            if (i === 1) labelText = `Tarif 1 personne (Individuel)`;
            else if (i === 2) labelText = `Tarif 2 personnes (Couple / Duo)`;
            else labelText = `Tarif ${i} personnes (Groupe / Famille)`;

            const fieldDiv = document.createElement('div');
            fieldDiv.style.background = '#252521';
            fieldDiv.style.border = '1px solid rgba(184, 154, 99, 0.4)';
            fieldDiv.style.borderRadius = '4px';
            fieldDiv.style.padding = '12px';

            fieldDiv.innerHTML = `
                <label style="display: block; font-size: 12px; font-weight: bold; color: #FFFFF0; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
                    👥 ${labelText}
                </label>
                <div style="display: flex; align-items: center;">
                    <input type="number" step="0.01" min="0" 
                           class="form-control ea-tarif-step-input" 
                           data-personnes="${i}" 
                           value="${currentVal}" 
                           placeholder="0.00" 
                           style="background: #111; border: 1px solid #B89A63; color: #B89A63; font-weight: bold; font-size: 15px; border-radius: 3px 0 0 3px; height: 38px;">
                    <span style="background: #B89A63; color: #111; font-weight: bold; padding: 0 12px; height: 38px; display: flex; align-items: center; border-radius: 0 3px 3px 0; font-size: 14px;">
                        €
                    </span>
                </div>
            `;

            fieldsContainer.appendChild(fieldDiv);
        }

        // Écouteurs sur chaque champ généré
        fieldsContainer.querySelectorAll('.ea-tarif-step-input').forEach(input => {
            input.addEventListener('input', updateJsonAndBase);
            input.addEventListener('change', updateJsonAndBase);
        });

        updateJsonAndBase();
    }

    function updateJsonAndBase() {
        const fields = document.querySelectorAll('.ea-tarif-step-input');
        const updatedTarifs = {};
        let firstPrice = null;

        fields.forEach(input => {
            const p = input.getAttribute('data-personnes');
            const val = parseFloat(input.value);
            if (!isNaN(val) && val >= 0) {
                updatedTarifs[p] = val;
                if (firstPrice === null) {
                    firstPrice = val;
                }
            }
        });

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
