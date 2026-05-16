// ========== АККОРДЕОН FAQ ==========

export function initFaq() {
    document.querySelectorAll('.faq__question').forEach(button => {
        button.addEventListener('click', () => {
            const currentItem = button.closest('.faq__item');
            const isActive = currentItem.classList.contains('active');
            
            document.querySelectorAll('.faq__item').forEach(item => {
                item.classList.remove('active');
            });
            
            if (!isActive) {
                currentItem.classList.add('active');
            }
        });
    });
}