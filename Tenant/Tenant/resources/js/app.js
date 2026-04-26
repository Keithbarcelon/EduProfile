import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import SetStudentStatusModal from './components/SetStudentStatusModal.vue';
import DocumentRequirementSettings from './components/DocumentRequirementSettings.vue';
import StudentDocumentUpload from './components/StudentDocumentUpload.vue';
import DocumentReview from './components/DocumentReview.vue';

window.Alpine = Alpine;

Alpine.start();

// Component Registration Helper
const mountComponent = (id, component, props = {}) => {
    const el = document.getElementById(id);
    if (el) {
        createApp(component, { ...props, ...el.dataset }).mount(`#${id}`);
    }
};

mountComponent('set-student-status-app', SetStudentStatusModal);
mountComponent('document-requirement-settings-app', DocumentRequirementSettings);
mountComponent('student-document-upload-app', StudentDocumentUpload);
mountComponent('document-review-app', DocumentReview);
