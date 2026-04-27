<template>
  <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-base font-semibold text-slate-800">Set Student Status</h3>
        <p class="text-sm text-slate-500">Choose a student, assign an allowed status, and provide a reason.</p>
      </div>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
        {{ roleLabel }}
      </span>
    </div>

    <form @submit.prevent="openConfirmation" class="grid grid-cols-1 gap-3 md:grid-cols-4">
      <div class="md:col-span-2">
        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Student</label>
        <select v-model="form.studentId" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-sky-500">
          <option value="">Select student</option>
          <option v-for="student in students" :key="student.id" :value="student.id">
            {{ student.full_name }} ({{ student.student_id }})
          </option>
        </select>
      </div>

      <div>
        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">New Status</label>
        <select v-model="form.statusId" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-sky-500">
          <option value="">Select status</option>
          <option v-for="status in allowedStatuses" :key="status.id" :value="status.id">
            {{ status.label }}
          </option>
        </select>
      </div>

      <div>
        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Reason</label>
        <input
          v-model="form.reason"
          type="text"
          maxlength="1000"
          placeholder="Reason for status change"
          class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-sky-500"
        />
      </div>

      <div class="md:col-span-4 flex items-center justify-end gap-3">
        <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>
        <button
          type="submit"
          :disabled="submitting"
          class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sky-700 disabled:cursor-not-allowed disabled:bg-sky-300"
        >
          {{ submitting ? 'Submitting...' : 'Set Status' }}
        </button>
      </div>
    </form>

    <div
      v-if="showConfirm"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
      role="dialog"
      aria-modal="true"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
        <h4 class="text-lg font-semibold text-slate-900">Confirm Status Change</h4>
        <p class="mt-2 text-sm text-slate-600">
          Change
          <span class="font-semibold text-slate-800">{{ selectedStudentName }}</span>
          to
          <span class="font-semibold text-slate-800">{{ selectedStatusLabel }}</span>?
        </p>
        <p class="mt-2 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
          Reason: {{ form.reason }}
        </p>

        <div class="mt-5 flex justify-end gap-3">
          <button
            type="button"
            class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200"
            @click="showConfirm = false"
          >
            Cancel
          </button>
          <button
            type="button"
            :disabled="submitting"
            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300"
            @click="submitStatusChange"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps({
  students: {
    type: Array,
    default: () => [],
  },
  allowedStatuses: {
    type: Array,
    default: () => [],
  },
  roleLabel: {
    type: String,
    default: 'Staff',
  },
  endpointTemplate: {
    type: String,
    required: true,
  },
});

const form = ref({
  studentId: '',
  statusId: '',
  reason: '',
});

const showConfirm = ref(false);
const submitting = ref(false);
const errorMessage = ref('');

const selectedStudent = computed(() => props.students.find((student) => Number(student.id) === Number(form.value.studentId)) || null);
const selectedStatus = computed(() => props.allowedStatuses.find((status) => Number(status.id) === Number(form.value.statusId)) || null);

const selectedStudentName = computed(() => selectedStudent.value ? `${selectedStudent.value.full_name} (${selectedStudent.value.student_id})` : 'selected student');
const selectedStatusLabel = computed(() => selectedStatus.value ? selectedStatus.value.label : 'selected status');

function openConfirmation() {
  errorMessage.value = '';

  if (!form.value.studentId) {
    errorMessage.value = 'Please select a student.';
    return;
  }

  if (!form.value.statusId) {
    errorMessage.value = 'Please select a status.';
    return;
  }

  if (!form.value.reason || form.value.reason.trim().length < 3) {
    errorMessage.value = 'Please provide a reason with at least 3 characters.';
    return;
  }

  showConfirm.value = true;
}

async function submitStatusChange() {
  if (submitting.value) {
    return;
  }

  submitting.value = true;
  errorMessage.value = '';

  const endpoint = props.endpointTemplate.replace('__STUDENT_ID__', String(form.value.studentId));

  try {
    await axios.post(endpoint, {
      status_id: Number(form.value.statusId),
      reason: form.value.reason.trim(),
    });

    showConfirm.value = false;
    window.location.reload();
  } catch (error) {
    const fallback = 'Unable to update student status. Please check role permissions and workflow rules.';
    const message = error?.response?.data?.message || fallback;
    errorMessage.value = String(message);
  } finally {
    submitting.value = false;
  }
}
</script>
