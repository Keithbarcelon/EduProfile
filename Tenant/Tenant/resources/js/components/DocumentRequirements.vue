<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <h2 class="text-2xl font-bold text-slate-800">Document Requirements</h2>
      <button 
        @click="isModalOpen = true; editingRequirement = null; resetForm()"
        class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700"
      >
        Create Requirement
      </button>
    </div>

    <!-- Requirements List -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div v-for="req in requirements" :key="req.id" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="font-bold text-slate-800">{{ req.name }}</h3>
            <span class="mt-1 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 capitalize">
              {{ req.required_for_status }}
            </span>
          </div>
          <div class="flex gap-2">
            <button @click="editReq(req)" class="text-slate-400 hover:text-sky-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
              </svg>
            </button>
            <button @click="deleteReq(req.id)" class="text-slate-400 hover:text-red-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        <p class="mt-3 text-sm text-slate-500 line-clamp-2">{{ req.description || 'No description provided.' }}</p>
      </div>
    </div>

    <!-- Create/Edit Modal (Alpine.js integration as requested) -->
    <div 
      v-show="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">{{ editingRequirement ? 'Edit' : 'Create' }} Requirement</h3>
        
        <form @submit.prevent="saveRequirement" class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase text-slate-500">Name</label>
            <input v-model="form.name" type="text" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase text-slate-500">Description</label>
            <textarea v-model="form.description" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500"></textarea>
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase text-slate-500">Required For Status</label>
            <select v-model="form.required_for_status" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
              <option value="all">All</option>
              <option value="regular">Regular</option>
              <option value="affirmative">Affirmative</option>
              <option value="probation">Probation</option>
            </select>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="isModalOpen = false" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancel</button>
            <button type="submit" :disabled="saving" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const requirements = ref([]);
const isModalOpen = ref(false);
const saving = ref(false);
const editingRequirement = ref(null);

const form = ref({
  name: '',
  description: '',
  required_for_status: 'all',
});

const fetchRequirements = async () => {
  const res = await axios.get('/api/requirements');
  requirements.value = res.data;
};

const saveRequirement = async () => {
  saving.value = true;
  try {
    if (editingRequirement.value) {
      await axios.put(`/api/requirements/${editingRequirement.value.id}`, form.value);
    } else {
      await axios.post('/api/requirements', form.value);
    }
    await fetchRequirements();
    isModalOpen.value = false;
  } catch (e) {
    alert('Error saving requirement');
  } finally {
    saving.value = false;
  }
};

const editReq = (req) => {
  editingRequirement.value = req;
  form.value = { ...req };
  isModalOpen.value = true;
};

const deleteReq = async (id) => {
  if (!confirm('Are you sure?')) return;
  await axios.delete(`/api/requirements/${id}`);
  await fetchRequirements();
};

const resetForm = () => {
  form.value = { name: '', description: '', required_for_status: 'all' };
};

onMounted(fetchRequirements);
</script>
