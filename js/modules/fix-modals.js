// Простой фикс для модальных окон
document.addEventListener('DOMContentLoaded', function() {
    // Все кнопки "Оставить заявку" в модалках услуг
    const closeAndOpenBtns = document.querySelectorAll('.close-and-open');
    
    closeAndOpenBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Закрываем текущую модалку
            const serviceModal = this.closest('.service-modal');
            if (serviceModal) {
                serviceModal.classList.remove('active');
            }
            
            // Открываем модалку заявки
            const callbackModal = document.getElementById('callbackModal');
            if (callbackModal) {
                callbackModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Кнопки "Оставить заявку" в слайдере
    const heroButtons = document.querySelectorAll('.open-form-btn');
    heroButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const callbackModal = document.getElementById('callbackModal');
            if (callbackModal) {
                callbackModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });
});