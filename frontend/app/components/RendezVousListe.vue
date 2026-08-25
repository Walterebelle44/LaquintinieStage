<script setup>
const props = defineProps({ titre: { type: String, required: true } })

const api = useApi()
const rendezVous = ref([])
const chargement = ref(true)
const filtreStatut = ref('')

const charger = async () => {
  chargement.value = true
  try {
    const params = {}
    if (filtreStatut.value) params.statut = filtreStatut.value
    const res = await api.get('/rendez-vous', params)
    rendezVous.value = res.data
  } finally {
    chargement.value = false
  }
}
onMounted(charger)
watch(filtreStatut, charger)

const formaterDateHeure = (d) => new Date(d).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' })
</script>

<template>
  <div>
    <AppHeader :titre="titre" />

    <div class="mt-2 mb-5">
      <select v-model="filtreStatut" class="input-field w-auto">
        <option value="">Tous les statuts</option>
        <option value="planifie">Planifiés</option>
        <option value="termine">Terminés</option>
        <option value="annule">Annulés</option>
      </select>
    </div>

    <div class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Date & heure</th>
            <th class="text-left px-4 py-3 font-medium">Stagiaire</th>
            <th class="text-left px-4 py-3 font-medium">Titre</th>
            <th class="text-left px-4 py-3 font-medium">Créé par</th>
            <th class="text-left px-4 py-3 font-medium">Statut</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="chargement"><td colspan="5" class="px-4 py-6 text-center text-slate-400">Chargement…</td></tr>
          <tr v-else-if="!rendezVous.length"><td colspan="5" class="px-4 py-6 text-center text-slate-400">Aucun rendez-vous.</td></tr>
          <tr v-for="r in rendezVous" :key="r.id" class="hover:bg-slate-50/60">
            <td class="px-4 py-3 whitespace-nowrap">{{ formaterDateHeure(r.date_heure) }}</td>
            <td class="px-4 py-3 font-medium text-slate-800">{{ r.stagiaire?.user?.prenom }} {{ r.stagiaire?.user?.nom }}</td>
            <td class="px-4 py-3 text-slate-600">{{ r.titre }}</td>
            <td class="px-4 py-3 text-slate-500">{{ r.cree_par?.prenom }} {{ r.cree_par?.nom }}</td>
            <td class="px-4 py-3"><StatutBadge :statut="r.statut" /></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
