// ========== КАСТОМНЫЙ ВЫПАДАЮЩИЙ СПИСОК ==========

export function initCustomSelect() {
    const customSelect = document.getElementById('customSelect');
    if (!customSelect) return;
    
    const trigger = customSelect.querySelector('.custom-select__trigger');
    const options = customSelect.querySelectorAll('.custom-select__option');
    const placeholder = customSelect.querySelector('.custom-select__placeholder');
    const hiddenInput = document.getElementById('ctaService');
    
    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        customSelect.classList.toggle('open');
    });
    
    options.forEach(option => {
        option.addEventListener('click', () => {
            const value = option.dataset.value;
            const text = option.textContent;
            
            placeholder.textContent = text;
            placeholder.classList.add('selected');
            
            if (hiddenInput) {
                hiddenInput.value = value;
            }
            
            options.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            
            customSelect.classList.remove('error');
            const errorSpan = document.getElementById('ctaServiceError');
            if (errorSpan) {
                errorSpan.textContent = '';
                errorSpan.classList.remove('show');
            }
            
            customSelect.classList.remove('open');
        });
    });
    
    document.addEventListener('click', () => {
        customSelect.classList.remove('open');
    });
}