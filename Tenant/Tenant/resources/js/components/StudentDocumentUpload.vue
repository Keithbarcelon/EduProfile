<template>
  <div class="space-y-6">
    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
      <div class="mb-6">
        <h3 class="text-lg font-bold text-slate-800">Document Requirements</h3>
        <p class="text-sm text-slate-500">Please upload the following documents to complete your profile.</p>
      </div>

      <div v-if="loading" class="flex justify-center py-8">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-sky-500 border-t-transparent"></div>
      </div>

      <div v-else-if="requirements.length === 0" class="rounded-lg bg-slate-50 py-8 text-center">
        <p class="text-slate-500">No documents are currently required for your status.</p>
      </div>

      <div v-else class="space-y-4">
        <div 
          v-for="requirement in requirements" 
          :key="requirement.id"
          class="flex flex-col gap-4 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <div class="flex-1">
            <h4 class="font-semibold text-slate-800">
              {{ requirement.name }}
              <span v-if="requirement.is_required" class="ml-2 text-xs font-bold text-red-500 uppercase">* Required</span>
            </h4>
            <p class="mt-1 text-sm text-slate-500">{{ requirement.description }}</p>
            
            <!-- Status Badge -->
            <div class="mt-2 flex items-center gap-2">
              <span 
                class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="getStatusClass(getUploadedDocument(requirement.id))"
              >
                {{ getStatusLabel(getUploadedDocument(requirement.id)) }}
              </span>
              <p v-if="getUploadedDocument(requirement.id)?.review_remarks" class="text-xs text-red-500">
                Remarks: {{ getUploadedDocument(requirement.id).review_remarks }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <a 
              v-if="getUploadedDocument(requirement.id)"
              :href="getDocUrl(getUploadedDocument(requirement.id).file_path)"
              target="_blank"
              class="text-sm font-medium text-sky-600 hover:text-sky-700"
            >
              View File
            </a>

            <label 
              :for="'file-' + requirement.id"
              class="cursor-pointer rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sky-700 disabled:cursor-not-allowed"
              :class="{ 'opacity-50 cursor-not-allowed': uploading === requirement.id }"
            >
              {{ uploading === requirement.id ? 'Uploading...' : (getUploadedDocument(requirement.id) ? 'Replace' : 'Upload') }}
              <input 
                :id="'file-' + requirement.id"
                type="file"
                class="hidden"
                accept=".pdf,.jpg,.jpeg,.png"
                @change="handleFileUpload($event, requirement.id)"
                :disabled="uploading === requirement.id"
              >
            </label>
          </div>
        </div>
      </div>
    </section>

    <p v-if="errorMessage" class="mt-4 text-center text-sm text-red-600">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  studentId: {
    type: [Number, String],
    required: true
  }
});

const loading = ref(true);
const uploading = ref(null);
const requirements = ref([]);
const uploads = ref([]);
const errorMessage = ref('');

async function fetchData() {
  loading.value = true;
  try {
    const [reqsRes, docsRes] = await Promise.all([
      axios.get('/api/requirements'),
      axios.get('/api/documents/student')
    ]);
    requirements.value = reqsRes.data;
    uploads.value = docsRes.data;
  } catch (error) {
    errorMessage.value = 'Failed to load documents.';
  } finally {
    loading.value = false;
  }
}

function getUploadedDocument(requirementId) {
  return uploads.value.find(doc => doc.requirement_id === requirementId);
}

function getStatusLabel(doc) {
  if (!doc) return 'Not Uploaded';
  return doc.status.charAt(0).toUpperCase() + doc.status.slice(1);
}

function getStatusClass(doc) {
  if (!doc) return 'bg-slate-100 text-slate-600';
  if (doc.status === 'approved') return 'bg-emerald-100 text-emerald-700';
  if (doc.status === 'rejected') return 'bg-red-100 text-red-700';
  return 'bg-amber-100 text-amber-700'; // pending
}

function getDocUrl(path) {
  return `/storage/${path}`;
}

async function handleFileUpload(event, requirementId) {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 10 * 1024 * 1024) {
    alert('File size exceeds 10MB limit.');
    return;
  }

  uploading.value = requirementId;
  errorMessage.value = '';

  const formData = new FormData();
  formData.append('file', file);
  formData.append('requirement_id', requirementId);

  try {
    await axios.post(`/api/documents/upload`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    await fetchData();
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to upload document.';
  } finally {
    uploading.value = null;
  }
}

onMounted(() => {
  fetchData();
});
</script>
