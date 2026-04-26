<template>
  <div class="space-y-6 p-6">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-slate-800">My Documents</h2>
      <p class="text-sm text-slate-500">View and upload your required documents below.</p>
    </div>

    <!-- Requirements Grid -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <div v-for="req in requirements" :key="req.id" class="flex flex-col rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-start justify-between">
          <div>
            <h3 class="font-bold text-slate-800">{{ req.name }}</h3>
            <p class="text-xs text-slate-500">{{ req.description }}</p>
          </div>
          <span 
            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wider"
            :class="getStatusBadgeClass(getDocForReq(req.id))"
          >
            {{ getDocStatus(req.id) }}
          </span>
        </div>

        <!-- Document Info / Preview -->
        <div v-if="getDocForReq(req.id)" class="mb-4 rounded-xl bg-slate-50 p-4">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Uploaded File</span>
            <a :href="'/storage/' + getDocForReq(req.id).file_path" target="_blank" class="text-xs font-bold text-sky-600 hover:underline">View</a>
          </div>
          <p v-if="getDocForReq(req.id).review_remarks" class="mt-2 text-xs text-red-500 italic">
            "{{ getDocForReq(req.id).review_remarks }}"
          </p>
        </div>

        <!-- Upload Action -->
        <div class="mt-auto">
          <label 
            class="group relative flex cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-slate-200 py-4 transition hover:border-sky-400"
            :class="{ 'opacity-50 pointer-events-none': uploading === req.id }"
          >
            <input 
              type="file" 
              class="hidden" 
              accept=".pdf,.jpg,.jpeg,.png"
              @change="handleFileUpload($event, req.id)"
            >
            <span class="text-sm font-semibold text-slate-500 group-hover:text-sky-600">
              {{ uploading === req.id ? 'Uploading...' : 'Upload New' }}
            </span>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const requirements = ref([]);
const uploads = ref([]);
const uploading = ref(null);

const fetchData = async () => {
  const [reqsRes, docsRes] = await Promise.all([
    axios.get('/api/requirements'),
    axios.get('/api/documents/student')
  ]);
  requirements.value = reqsRes.data;
  uploads.value = docsRes.data;
};

const getDocForReq = (reqId) => {
  return uploads.value.find(doc => doc.requirement_id === reqId);
};

const getDocStatus = (reqId) => {
  const doc = getDocForReq(reqId);
  if (!doc) return 'Missing';
  return doc.status;
};

const getStatusBadgeClass = (doc) => {
  if (!doc) return 'bg-slate-100 text-slate-500';
  switch (doc.status) {
    case 'approved': return 'bg-emerald-100 text-emerald-600';
    case 'rejected': return 'bg-red-100 text-red-600';
    case 'pending': return 'bg-amber-100 text-amber-600';
    default: return 'bg-slate-100 text-slate-500';
  }
};

const handleFileUpload = async (event, reqId) => {
  const file = event.target.files[0];
  if (!file) return;

  uploading.value = reqId;
  const formData = new FormData();
  formData.append('file', file);
  formData.append('requirement_id', reqId);

  try {
    await axios.post('/api/documents/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    await fetchData();
  } catch (e) {
    alert(e.response?.data?.message || 'Upload failed');
  } finally {
    uploading.value = null;
  }
};

onMounted(fetchData);
</script>
