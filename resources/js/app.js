import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

import './modules/maps.js';
import { initReveal } from './modules/reveal.js';

// Init reveal animations
initReveal();

// Vue 3 Forms
import { createApp } from 'vue';
import ApplicationForm from './components/ApplicationForm.vue';
import LostAndFoundForm from './components/LostAndFoundForm.vue';
import FoundItemForm from './components/FoundItemForm.vue';

// Mount Vue application form on elements with data-application-form attribute
document.querySelectorAll('[data-application-form]').forEach((el) => {
    const jobId = el.dataset.jobId;
    const variant = el.dataset.variant || 'light-blue';
    const app = createApp(ApplicationForm, { jobId, variant });
    app.mount(el);
});

// Mount Vue lost and found form on elements with data-lost-and-found-form attribute
document.querySelectorAll('[data-lost-and-found-form]').forEach((el) => {
    const variant = el.dataset.variant || 'white';
    const app = createApp(LostAndFoundForm, { variant });
    app.mount(el);
});

// Mount Vue found item form (Fundmeldung fürs Personal); Konfiguration als JSON im Template
document.querySelectorAll('[data-found-item-form]').forEach((el) => {
    const config = JSON.parse(document.getElementById('found-item-form-config')?.textContent || '{}');
    const variant = el.dataset.variant || 'light-blue';
    const app = createApp(FoundItemForm, { config, variant });
    app.mount(el);
});

// Accident report (Unfallmeldung); config as JSON in the template. Loaded only on its pages (large).
const accidentReport = document.querySelector('[data-accident-report]');
if (accidentReport) {
    import('./components/accident-report/AccidentReportApp.vue').then(({ default: AccidentReportApp }) => {
        const config = JSON.parse(document.getElementById('accident-report-config')?.textContent || '{}');
        const app = createApp(AccidentReportApp, { config });
        app.provide('formVariant', 'light-blue');
        app.mount(accidentReport);
    });
}
