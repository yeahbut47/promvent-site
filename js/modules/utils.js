// ========== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ==========

export function isValidName(name) {
    if (!name) return false;
    const nameRegex = /^[а-яА-ЯёЁa-zA-Z\s\-]{2,30}$/;
    return nameRegex.test(name.trim());
}

export function isValidRussianPhone(phone) {
    if (!phone) return false;
    const digits = phone.replace(/\D/g, '');
    if (digits.length === 11 && (digits.startsWith('7') || digits.startsWith('8'))) return true;
    if (digits.length === 10 && digits.startsWith('9')) return true;
    return false;
}

export function isValidQuestion(question) {
    if (!question) return false;
    const trimmed = question.trim();
    return trimmed.length >= 10 && trimmed.length <= 1000;
}

export function formatPhone(phone) {
    const digits = phone.replace(/\D/g, '');
    if (digits.length === 11) {
        return `+7 (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7, 9)}-${digits.slice(9, 11)}`;
    }
    if (digits.length === 10) {
        return `+7 (${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 8)}-${digits.slice(8, 10)}`;
    }
    return phone;
}

export function applyPhoneMask(input) {
    if (!input) return;
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        
        let formatted = '';
        if (value.length === 0) {
            formatted = '';
        } else if (value.length === 1) {
            formatted = '+7';
        } else if (value.length <= 4) {
            formatted = `+7 (${value.slice(1)}`;
        } else if (value.length <= 7) {
            formatted = `+7 (${value.slice(1, 4)}) ${value.slice(4)}`;
        } else if (value.length <= 9) {
            formatted = `+7 (${value.slice(1, 4)}) ${value.slice(4, 7)}-${value.slice(7)}`;
        } else {
            formatted = `+7 (${value.slice(1, 4)}) ${value.slice(4, 7)}-${value.slice(7, 9)}-${value.slice(9, 11)}`;
        }
        e.target.value = formatted;
    });
}

export function setButtonLoading(button, isLoading) {
    if (!button) return;
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Отправка...';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || 'Отправить';
    }
}

export function showError(input, errorSpan, message) {
    if (input) input.classList.add('error');
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.classList.add('show');
    }
}

export function hideError(input, errorSpan) {
    if (input) input.classList.remove('error');
    if (errorSpan) {
        errorSpan.textContent = '';
        errorSpan.classList.remove('show');
    }
}