import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

import './modules/maps.js';

// Vue 3 Application Form
import { createApp } from 'vue';
import ApplicationForm from './components/ApplicationForm.vue';

// Mount Vue application form on elements with data-application-form attribute
document.querySelectorAll('[data-application-form]').forEach((el) => {
    const jobId = el.dataset.jobId;
    const app = createApp(ApplicationForm, { jobId });
    app.mount(el);
});
