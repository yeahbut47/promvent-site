// ========== ФОРМЫ ==========
import { 
    isValidName, isValidRussianPhone, isValidQuestion, 
    formatPhone, applyPhoneMask, setButtonLoading, 
    showError, hideError 
} from './utils.js';
import { showNotification } from './modal.js';

// ========== ОБЩАЯ ФУНКЦИЯ ДЛЯ ОТПРАВКИ НА БЭКЕНД ==========
async function submitForm(formData, action) {
    try {
        const response = await fetch('/backend/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: action,
                ...formData
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Успешно!', result.message);
            return true;
        } else {
            showNotification('Ошибка', result.message, false);
            return false;
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Ошибка', 'Произошла ошибка при отправке. Попробуйте позже.', false);
        return false;
    }
}

// ========== ФОРМА ВОПРОСА ИЗ FAQ ==========
export function initFaqForm() {
    const sendQuestionBtn = document.getElementById('sendQuestionBtn');
    const faqQuestionForm = document.getElementById('faqQuestionForm');
    const faqQuestion = document.getElementById('faqQuestion');
    const faqName = document.getElementById('faqName');
    const faqPhone = document.getElementById('faqPhone');
    const faqQuestionError = document.getElementById('faqQuestionError');
    const faqNameError = document.getElementById('faqNameError');
    const faqPhoneError = document.getElementById('faqPhoneError');
    
    if (!sendQuestionBtn || !faqQuestionForm) return;
    
    applyPhoneMask(faqPhone);
    
    sendQuestionBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        let isValid = true;
        
        const question = faqQuestion?.value.trim() || '';
        const name = faqName?.value.trim() || '';
        let phone = faqPhone?.value.trim() || '';
        
        phone = formatPhone(phone);
        if (faqPhone) faqPhone.value = phone;
        
        hideError(faqQuestion, faqQuestionError);
        hideError(faqName, faqNameError);
        hideError(faqPhone, faqPhoneError);
        
        if (question.length < 10) {
            showError(faqQuestion, faqQuestionError, 'Вопрос должен содержать минимум 10 символов');
            isValid = false;
        } else if (question.length > 1000) {
            showError(faqQuestion, faqQuestionError, 'Вопрос не должен превышать 1000 символов');
            isValid = false;
        }
        
        if (!isValidName(name)) {
            showError(faqName, faqNameError, 'Введите корректное имя (от 2 до 30 букв)');
            isValid = false;
        }
        
        if (!isValidRussianPhone(phone)) {
            showError(faqPhone, faqPhoneError, 'Введите номер телефона (например: +7 999 999-99-99)');
            isValid = false;
        }
        
        if (isValid) {
            setButtonLoading(sendQuestionBtn, true);
            
            // Отправляем на бэкенд
            const success = await submitForm({
                name: name,
                phone: phone,
                question: question
            }, 'send_faq');
            
            setButtonLoading(sendQuestionBtn, false);
            
            if (success) {
                faqQuestionForm.reset();
            }
        }
    });
    
    faqQuestion?.addEventListener('focus', () => hideError(faqQuestion, faqQuestionError));
    faqName?.addEventListener('focus', () => hideError(faqName, faqNameError));
    faqPhone?.addEventListener('focus', () => hideError(faqPhone, faqPhoneError));
}

// ========== ФОРМА "ГОТОВЫ НАЧАТЬ РЕМОНТ?" ==========
export function initCtaForm() {
    const ctaForm = document.getElementById('ctaForm');
    const ctaName = document.getElementById('ctaName');
    const ctaPhone = document.getElementById('ctaPhone');
    const ctaSubmitBtn = document.getElementById('ctaSubmitBtn');
    const ctaNameError = document.getElementById('ctaNameError');
    const ctaPhoneError = document.getElementById('ctaPhoneError');
    const ctaServiceError = document.getElementById('ctaServiceError');
    const customSelect = document.getElementById('customSelect');
    const hiddenInput = document.getElementById('ctaService');
    
    if (!ctaForm) return;
    
    applyPhoneMask(ctaPhone);
    
    ctaForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        let isValid = true;
        
        const name = ctaName?.value.trim() || '';
        let phone = ctaPhone?.value.trim() || '';
        const service = hiddenInput?.value || '';
        
        // Получаем текст услуги для отображения
        let serviceText = '';
        if (customSelect) {
            const selectedOption = customSelect.querySelector('.custom-select__option.selected');
            serviceText = selectedOption ? selectedOption.textContent : '';
        }
        
        phone = formatPhone(phone);
        if (ctaPhone) ctaPhone.value = phone;
        
        hideError(ctaName, ctaNameError);
        hideError(ctaPhone, ctaPhoneError);
        hideError(null, ctaServiceError);
        if (customSelect) customSelect.classList.remove('error');
        
        if (!isValidName(name)) {
            showError(ctaName, ctaNameError, 'Введите корректное имя (от 2 до 30 букв)');
            isValid = false;
        }
        
        if (!isValidRussianPhone(phone)) {
            showError(ctaPhone, ctaPhoneError, 'Введите номер телефона (например: +7 999 999-99-99)');
            isValid = false;
        }
        
        if (!service) {
            if (customSelect) customSelect.classList.add('error');
            showError(null, ctaServiceError, 'Выберите тип оборудования');
            isValid = false;
        }
        
        if (isValid) {
            setButtonLoading(ctaSubmitBtn, true);
            
            // Отправляем на бэкенд
            const success = await submitForm({
                name: name,
                phone: phone,
                service: service
            }, 'send_cta');
            
            setButtonLoading(ctaSubmitBtn, false);
            
            if (success) {
                ctaForm.reset();
                
                // Очистить кастомный селект
                if (customSelect) {
                    const placeholder = customSelect.querySelector('.custom-select__placeholder');
                    if (placeholder) {
                        placeholder.textContent = 'Что нужно отремонтировать?';
                        placeholder.classList.remove('selected');
                    }
                    const options = customSelect.querySelectorAll('.custom-select__option');
                    options.forEach(opt => opt.classList.remove('selected'));
                    if (hiddenInput) hiddenInput.value = '';
                    customSelect.classList.remove('error');
                }
            }
        }
    });
    
    ctaName?.addEventListener('focus', () => hideError(ctaName, ctaNameError));
    ctaPhone?.addEventListener('focus', () => hideError(ctaPhone, ctaPhoneError));
}

// ========== ФОРМА ОБРАТНОГО ЗВОНКА (МОДАЛЬНОЕ ОКНО) ==========
export function initCallbackForm() {
    const submitBtn = document.getElementById('submitFakeBtn');
    const modalName = document.getElementById('modalName');
    const modalPhone = document.getElementById('modalPhone');
    const modalNameError = document.getElementById('modalNameError');
    const modalPhoneError = document.getElementById('modalPhoneError');
    const modal = document.getElementById('callbackModal');
    
    if (!submitBtn) return;
    
    applyPhoneMask(modalPhone);
    
    submitBtn.addEventListener('click', async () => {
        let isValid = true;
        
        const name = modalName?.value.trim() || '';
        let phone = modalPhone?.value.trim() || '';
        
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
            const success = await submitForm({
                name: name,
                phone: phone
            }, 'send_callback');
            
            setButtonLoading(submitBtn, false);
            
            if (success && modal) {
                // Закрываем модальное окно
                modal.style.display = 'none';
                document.body.style.overflow = '';
                
                // Очищаем поля
                if (modalName) modalName.value = '';
                if (modalPhone) modalPhone.value = '';
            }
        }
    });
    
    modalName?.addEventListener('focus', () => hideError(modalName, modalNameError));
    modalPhone?.addEventListener('focus', () => hideError(modalPhone, modalPhoneError));
}