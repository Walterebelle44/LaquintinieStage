<script setup>
definePageMeta({ layout: 'app' })

const api = useApi()
const donnees = ref(null)
const chargement = ref(true)

onMounted(async () => {
  try {
    donnees.value = await api.get('/mon-espace/feuille-de-route')
  } finally {
    chargement.value = false
  }
})

const formaterDate = (d) => new Date(d).toLocaleDateString('fr-FR', { weekday: 'short', day: '2-digit', month: 'short' })

const stats = computed(() => {
  const presences = donnees.value?.presences || []
  return {
    present: presences.filter((p) => p.statut === 'present').length,
    retard: presences.filter((p) => p.statut === 'retard').length,
    absent: presences.filter((p) => p.statut === 'absent').length,
  }
})
</script>

<template>
  <div>
    <AppHeader titre="Mes présences" />

    <div v-if="chargement" class="text-slate-400 text-sm mt-6">Chargement…</div>

    <div v-else class="mt-2">
      <div class="grid grid-cols-3 gap-4 mb-5">
        <StatCard label="Présences" :valeur="stats.present" couleur="emerald" />
        <StatCard label="Retards" :valeur="stats.retard" couleur="amber" />
        <StatCard label="Absences" :valeur="stats.absent" couleur="red" />
      </div>

      <div class="card overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="text-left px-4 py-3 font-medium">Date</th>
              <th class="text-left px-4 py-3 font-medium">Arrivée</th>
              <th class="text-left px-4 py-3 font-medium">Départ</th>
              <th class="text-left px-4 py-3 font-medium">Statut</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="!donnees.presences?.length"><td colspan="4" class="px-4 py-6 text-center text-slate-400">Aucune présence enregistrée.</td></tr>
            <tr v-for="p in donnees.presences" :key="p.id">
              <td class="px-4 py-3 capitalize">{{ formaterDate(p.date) }}</td>
              <td class="px-4 py-3">{{ p.heure_arrivee || '—' }}</td>
              <td class="px-4 py-3">{{ p.heure_depart || '—' }}</td>
              <td class="px-4 py-3"><StatutBadge :statut="p.statut" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
