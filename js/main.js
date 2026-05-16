// ========== ГЛАВНЫЙ ФАЙЛ ==========
// Импорт всех модулей
import { initMobileMenu } from './modules/mobileMenu.js';
import { initHeroSlider } from './modules/slider.js';
import { initProjectsSlider } from './modules/projectsSlider.js';
import { initFaq } from './modules/faq.js';
import { initCallbackModal, initServiceModals, initNotificationModal } from './modules/modal.js';
import { initFaqForm, initCtaForm } from './modules/forms.js';
import { initSmoothScroll } from './modules/smoothScroll.js';
import { initBackToTop } from './modules/backToTop.js';
import { initCustomSelect } from './modules/customSelect.js';
import { initPrivacy } from './modules/privacy.js';

// Инициализация всех компонентов при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initHeroSlider();
    initProjectsSlider();
    initFaq();
    initCallbackModal();
    initServiceModals();
    initNotificationModal();
    initFaqForm();
    initCtaForm();
    initSmoothScroll();
    initBackToTop();
    initCustomSelect();
    initPrivacy();
});