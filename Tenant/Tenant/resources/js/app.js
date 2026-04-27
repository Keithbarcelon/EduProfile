import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import SetStudentStatusModal from './components/SetStudentStatusModal.vue';

window.Alpine = Alpine;

Alpine.start();

const statusMount = document.getElementById('set-student-status-app');

if (statusMount) {
	const students = JSON.parse(statusMount.dataset.students || '[]');
	const allowedStatuses = JSON.parse(statusMount.dataset.allowedStatuses || '[]');
	const roleLabel = statusMount.dataset.roleLabel || 'Staff';
	const endpointTemplate = statusMount.dataset.endpointTemplate || '/api/students/__STUDENT_ID__/status';

	createApp(SetStudentStatusModal, {
		students,
		allowedStatuses,
		roleLabel,
		endpointTemplate,
	}).mount('#set-student-status-app');
}
