<template>
  <div class="space-y-6">
    <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h3 class="text-lg font-bold text-slate-800">Manage Required Documents</h3>
          <p class="text-sm text-slate-500">Define which documents students need to upload based on their status.</p>
        </div>
        <button 
          @click="openModal()"
          class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sky-700"
        >
          Add New Requirement
        </button>
      </div>

      <div v-if="loading" class="flex justify-center py-8">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-sky-500 border-t-transparent"></div>
      </div>

      <div v-else class="overflow-hidden rounded-xl border border-slate-200">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
            <tr>
              <th class="px-6 py-4">Document Name</th>
              <th class="px-6 py-4">Applicable Status</th>
              <th class="px-6 py-4">Required?</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-for="req in requirements" :key="req.id" class="hover:bg-slate-50">
              <td class="px-6 py-4">
                <div class="font-medium text-slate-800">{{ req.name }}</div>
                <div class="text-xs text-slate-500">{{ req.description }}</div>
              </td>
              <td class="px-6 py-4 capitalize">{{ req.applicable_status }}</td>
              <td class="px-6 py-4">
                <span 
                  :class="req.is_required ? 'text-red-600 font-bold' : 'text-slate-400'"
                >
                  {{ req.is_required ? 'Yes' : 'No' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button @click="openModal(req)" class="text-sky-600 hover:text-sky-800 mr-3">Edit</button>
                <button @click="deleteRequirement(req.id)" class="text-red-600 hover:text-red-800">Delete</button>
              </td>
            </tr>
            <tr v-if="requirements.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-slate-500">No requirements defined yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4">
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
        <h4 class="text-lg font-semibold text-slate-900">{{ editingId ? 'Edit Requirement' : 'Add New Requirement' }}</h4>
        
        <form @submit.prevent="saveRequirement" class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase text-slate-500">Document Name</label>
            <input v-model="form.name" type="text" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase text-slate-500">Description</label>
            <textarea v-model="form.description" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500"></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase text-slate-500">Applicable Status</label>
            <select v-model="form.applicable_status" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
              <option value="all">All Statuses</option>
              <option value="regular">Regular</option>
              <option value="affirmative">Affirmative (Admission)</option>
              <option value="probation">Probation (Faculty)</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="form.is_required" type="checkbox" id="is_req" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
            <label for="is_req" class="text-sm text-slate-700">Mark as mandatory</label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="showModal = false" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancel</button>
            <button type="submit" :disabled="saving" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 disabled:bg-sky-300">
              {{ saving ? 'Saving...' : 'Save Requirement' }}
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
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const editingId = ref(null);

const form = ref({
  name: '',
  description: '',
  applicable_status: 'all',
  is_required: true,
});

async function fetchRequirements() {
  loading.value = true;
  try {
    const response = await axios.get('/api/document-requirements');
    requirements.value = response.data;
  } catch (error) {
    console.error('Failed to fetch requirements');
  } finally {
    loading.value = false;
  }
}

function openModal(req = null) {
  if (req) {
    editingId.value = req.id;
    form.value = { ...req };
  } else {
    editingId.value = null;
    form.value = { name: '', description: '', applicable_status: 'all', is_required: true };
  }
  showModal.value = true;
}

async function saveRequirement() {
  saving.value = true;
  try {
    if (editingId.value) {
      await axios.put(`/api/document-requirements/${editingId.value}`, form.value);
    } else {
      await axios.post('/api/document-requirements', form.value);
    }
    showModal.value = false;
    await fetchRequirements();
  } catch (error) {
    alert('Failed to save requirement');
  } finally {
    saving.value = false;
  }
}

async function deleteRequirement(id) {
  if (!confirm('Are you sure you want to delete this requirement?')) return;
  try {
    await axios.delete(`/api/document-requirements/${id}`);
    await fetchRequirements();
  } catch (error) {
    alert('Failed to delete requirement');
  }
}

onMounted(fetchRequirements);
</script>
