// ========== ПОЛИТИКА КОНФИДЕНЦИАЛЬНОСТИ ==========

export function initPrivacy() {
    function openPrivacy() {
        const modal = document.getElementById('privacyModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closePrivacy() {
        const modal = document.getElementById('privacyModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    
    // Делаем функцию глобальной для onclick в HTML
    window.openPrivacy = openPrivacy;
    
    const modal = document.getElementById('privacyModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closePrivacy();
            }
        });
    }
    
    const closeBtn = document.getElementById('closePrivacyBtn');
    if (closeBtn) {
        closeBtn.onclick = closePrivacy;
    }
}