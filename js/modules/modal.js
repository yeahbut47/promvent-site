// ========== МОДАЛЬНЫЕ ОКНА ==========
import { formatPhone, isValidName, isValidRussianPhone, showError, hideError, setButtonLoading, applyPhoneMask } from './utils.js';

let notificationModal, notificationTitle, notificationMessage;

// ========== УВЕДОМЛЕНИЯ ==========
export function showNotification(title, message, isSuccess = true) {
    if (!notificationModal) return;
    
    notificationTitle.textContent = title;
    notificationMessage.textContent = message;
    
    const iconDiv = notificationModal.querySelector('div[style*="font-size: 3rem"]');
    if (iconDiv) {
        if (isSuccess) {
            iconDiv.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #1f6392;"></i>';
        } else {
            iconDiv.innerHTML = '<i class="bi bi-exclamation-circle-fill" style="color: #e74c3c;"></i>';
        }
    }
    
    notificationModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeNotificationModal() {
    if (notificationModal) {
        notificationModal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ========== МОДАЛЬНОЕ ОКНО ЗАЯВКИ ==========
export function initCallbackModal() {
    const modal = document.getElementById('callbackModal');
    const modalSlideInfo = document.getElementById('modalSlideInfo');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const submitBtn = document.getElementById('submitFakeBtn');
    const modalName = document.getElementById('modalName');
    const modalPhone = document.getElementById('modalPhone');
    const modalNameError = document.getElementById('modalNameError');
    const modalPhoneError = document.getElementById('modalPhoneError');
    
    if (!modal) return;
    
    // Маска для телефона
    if (modalPhone) {
        import('./utils.js').then(utils => {
            utils.applyPhoneMask(modalPhone);
        });
    }
    
    function getCurrentServiceText() {
        let index = 0;
        const activeDot = document.querySelector('.hero__dot.active-dot');
        if (activeDot) index = parseInt(activeDot.dataset.index);
        
        const services = [
            'ремонт промышленной вентиляции',
            'настройку и ремонт автоматики',
            'ремонт промышленных кондиционеров'
        ];
        return services[index] || 'ремонт оборудования';
    }
    
    function openModal() {
        modalSlideInfo.textContent = `Заявка на ${getCurrentServiceText()}. Оставьте контакты, и мы свяжемся с вами.`;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        if (modalName) {
            modalName.value = '';
            modalName.classList.remove('error');
        }
        if (modalPhone) {
            modalPhone.value = '';
            modalPhone.classList.remove('error');
        }
        
        if (modalNameError) {
            modalNameError.textContent = '';
            modalNameError.classList.remove('show');
        }
        if (modalPhoneError) {
            modalPhoneError.textContent = '';
            modalPhoneError.classList.remove('show');
        }
    }
    
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Обработчик отправки формы
    if (submitBtn) {
        submitBtn.addEventListener('click', async () => {
            let isValid = true;
            const name = modalName?.value.trim() || '';
            let phone = modalPhone?.value.trim() || '';
            
            // Импортируем функции валидации
            const { formatPhone, isValidName, isValidRussianPhone, showError, hideError, setButtonLoading, showNotification } = await import('./utils.js');
            
            phone = formatPhone(phone);
            if (modalPhone) modalPhone.value = phone;
            
            hideError(modalName, modalNameError);
            hideError(modalPhone, modalPhoneError);
            
            if (!isValidName(name)) {
                showError(modalName, modalNameError, 'Введите корректное имя (от 2 до 30 букв)');
                isValid = false;
            }
            
            if (!isValidRussianPhone(phone)) {
                showError(modalPhone, modalPhoneError, 'Введите номер телефона (например: +7 999 999-99-99)');
                isValid = false;
            }
            
            if (isValid) {
                setButtonLoading(submitBtn, true);
                
                // Отправляем на бэкенд
                try {
                    const response = await fetch('/backend/api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'send_callback',
                            name: name,
                            phone: phone
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        const { showNotification } = await import('./modal.js');
                        showNotification('Успешно!', result.message);
                        closeModal();
                    } else {
                        const { showNotification } = await import('./modal.js');
                        showNotification('Ошибка', result.message, false);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    const { showNotification } = await import('./modal.js');
                    showNotification('Ошибка', 'Произошла ошибка при отправке. Попробуйте позже.', false);
                }
                
                setButtonLoading(submitBtn, false);
            }
        });
    }
    
    // Открытие модалки при клике на кнопки
    document.querySelectorAll('.open-form-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });
    });
    
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.style.display === 'flex') closeModal(); });
}

// ========== МОДАЛЬНЫЕ ОКНА УСЛУГ ==========
export function initServiceModals() {
    document.querySelectorAll('.open-details').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    document.querySelectorAll('.service-modal__close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.service-modal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
    
    document.querySelectorAll('.service-modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    document.querySelectorAll('.close-and-open').forEach(btn => {
        btn.addEventListener('click', function() {
            // Закрываем модалку услуги
            const currentModal = this.closest('.service-modal');
            if (currentModal) {
                currentModal.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Открываем модалку обратной связи
            const callbackModal = document.getElementById('callbackModal');
            if (callbackModal) {
                callbackModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                // Обновляем текст в модалке
                const modalSlideInfo = document.getElementById('modalSlideInfo');
                if (modalSlideInfo) {
                    // Определяем, из какой услуги пришли
                    let serviceText = '';
                    if (currentModal) {
                        const title = currentModal.querySelector('.service-modal__title');
                        if (title) {
                            serviceText = title.textContent.toLowerCase();
                        }
                    }
                    modalSlideInfo.textContent = `Заявка на ${serviceText || 'ремонт оборудования'}. Оставьте контакты, и мы свяжемся с вами.`;
                }
            }
        });
    });
}

// ========== ИНИЦИАЛИЗАЦИЯ УВЕДОМЛЕНИЙ ==========
export function initNotificationModal() {
    notificationModal = document.getElementById('notificationModal');
    notificationTitle = document.getElementById('notificationTitle');
    notificationMessage = document.getElementById('notificationMessage');
    
    const closeNotificationBtn = document.getElementById('closeNotificationBtn');
    const closeNotificationBtn2 = document.getElementById('closeNotificationBtn2');
    
    if (closeNotificationBtn) closeNotificationBtn.addEventListener('click', closeNotificationModal);
    if (closeNotificationBtn2) closeNotificationBtn2.addEventListener('click', closeNotificationModal);
    
    notificationModal?.addEventListener('click', (e) => {
        if (e.target === notificationModal) closeNotificationModal();
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && notificationModal?.style.display === 'flex') closeNotificationModal();
    });
}