// ========== МОБИЛЬНОЕ МЕНЮ ==========

export function initMobileMenu() {
    const toggleBtn = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (!toggleBtn || !mobileMenu) return;
    
    toggleBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
        document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
    });
    
    document.querySelectorAll('.mobile-nav__link, .mobile-phone').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
}