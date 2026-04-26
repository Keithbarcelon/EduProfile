<template>
  <div class="p-6">
    <h2 class="mb-6 text-2xl font-bold text-slate-800">Document Review Panel</h2>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
          <tr>
            <th class="px-6 py-4">Student</th>
            <th class="px-6 py-4">Document Type</th>
            <th class="px-6 py-4">File</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr v-for="doc in pendingDocuments" :key="doc.id" class="hover:bg-slate-50">
            <td class="px-6 py-4 font-medium text-slate-800">
              {{ doc.student?.first_name }} {{ doc.student?.last_name }}
            </td>
            <td class="px-6 py-4">{{ doc.requirement?.name }}</td>
            <td class="px-6 py-4">
              <a :href="'/storage/' + doc.file_path" target="_blank" class="text-sky-600 hover:underline">Download</a>
            </td>
            <td class="px-6 py-4">
              <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-600 uppercase">
                {{ doc.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <button 
                @click="reviewDoc(doc, 'approved')"
                class="mr-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700"
              >
                Approve
              </button>
              <button 
                @click="reviewDoc(doc, 'rejected')"
                class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-red-700"
              >
                Reject
              </button>
            </td>
          </tr>
          <tr v-if="pendingDocuments.length === 0">
            <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">No pending documents to review.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const pendingDocuments = ref([]);

const fetchPending = async () => {
  // In a real app, you might want a specific 'review' endpoint. 
  // For this demo, we'll fetch all student documents and filter pending.
  const res = await axios.get('/api/documents/student'); 
  pendingDocuments.value = res.data.filter(d => d.status === 'pending');
};

const reviewDoc = async (doc, status) => {
  const remarks = status === 'rejected' ? prompt('Enter reason for rejection:') : null;
  if (status === 'rejected' && remarks === null) return;

  try {
    await axios.post('/api/documents/review', {
      document_id: doc.id,
      status: status,
      remarks: remarks
    });
    await fetchPending();
  } catch (e) {
    alert('Review failed');
  }
};

onMounted(fetchPending);
</script>
