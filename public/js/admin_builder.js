document.addEventListener('DOMContentLoaded', function() {
    const previewIframe = document.getElementById('builderPreviewIframe');
    const previewArea = document.getElementById('builderPreviewArea');
    const toggleDrawerBtn = document.getElementById('builderToggleDrawerBtn');
    const refreshBtn = document.getElementById('builderRefreshBtn');
    const deviceBtns = document.querySelectorAll('.builder-device-btn');

    if (!previewIframe) return;

    // 1. Swapping modes (Desktop / Tablette / Mobile)
    deviceBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            deviceBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const mode = this.getAttribute('data-device');
            previewIframe.className = 'builder-preview-iframe mode-' + mode;
        });
    });

    // 2. Bouton Rafraîchir
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            previewIframe.src = previewIframe.src;
        });
    }

    // 3. Tiroir pour petits écrans (Tablette/Mobile)
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
});
