<script setup>
definePageMeta({ layout: 'app' })

const api = useApi()
const logs = ref([])
const pagination = ref(null)
const chargement = ref(true)
const page = ref(1)

const charger = async () => {
  chargement.value = true
  try {
    const res = await api.get('/admin/logs', { page: page.value, per_page: 25 })
    logs.value = res.data
    pagination.value = res
  } finally {
    chargement.value = false
  }
}

onMounted(charger)
watch(page, charger)

const formaterDate = (d) => new Date(d).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' })
</script>

<template>
  <div>
    <AppHeader titre="Journal d'activité" />

    <div class="card overflow-hidden mt-2">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Date</th>
            <th class="text-left px-4 py-3 font-medium">Auteur</th>
            <th class="text-left px-4 py-3 font-medium">Catégorie</th>
            <th class="text-left px-4 py-3 font-medium">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="chargement"><td colspan="4" class="px-4 py-6 text-center text-slate-400">Chargement…</td></tr>
          <tr v-else-if="!logs.length"><td colspan="4" class="px-4 py-6 text-center text-slate-400">Aucune activité enregistrée.</td></tr>
          <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/60">
            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ formaterDate(log.created_at) }}</td>
            <td class="px-4 py-3 font-medium text-slate-700">
              {{ log.causer ? `${log.causer.prenom} ${log.causer.nom}` : 'Système' }}
            </td>
            <td class="px-4 py-3">
              <span class="badge bg-slate-100 text-slate-600 capitalize">{{ log.log_name }}</span>
            </td>
            <td class="px-4 py-3 text-slate-600">{{ log.description }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="pagination && pagination.last_page > 1" class="flex justify-center gap-2 mt-4">
      <button class="btn-secondary" :disabled="page <= 1" @click="page--">Précédent</button>
      <span class="flex items-center px-3 text-sm text-slate-500">Page {{ page }} / {{ pagination.last_page }}</span>
      <button class="btn-secondary" :disabled="page >= pagination.last_page" @click="page++">Suivant</button>
    </div>
  </div>
</template>
