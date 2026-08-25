<script setup>
definePageMeta({ layout: 'app' })

const api = useApi()
const stats = ref(null)
const chargement = ref(true)

onMounted(async () => {
  try {
    stats.value = await api.get('/admin/dashboard')
  } finally {
    chargement.value = false
  }
})
</script>

<template>
  <div>
    <AppHeader titre="Tableau de bord administrateur" />

    <div v-if="chargement" class="text-slate-400 text-sm mt-6">Chargement des statistiques…</div>

    <div v-else-if="stats" class="mt-2 space-y-8">
      <section>
        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Comptes de la plateforme</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard label="Utilisateurs" :valeur="stats.total_utilisateurs" couleur="brand" />
          <StatCard label="Administrateurs" :valeur="stats.total_admins" couleur="brand" />
          <StatCard label="Encadrants" :valeur="stats.total_encadrants" couleur="brand" />
          <StatCard label="Comptes bloqués" :valeur="stats.comptes_bloques" couleur="red" />
        </div>
      </section>

      <section>
        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Stagiaires</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard label="Total stagiaires" :valeur="stats.total_stagiaires" couleur="brand" />
          <StatCard label="Parcours académique" :valeur="stats.stagiaires_academiques" couleur="brand" />
          <StatCard label="Parcours professionnel" :valeur="stats.stagiaires_professionnels" couleur="brand" />
          <StatCard label="En cours" :valeur="stats.stagiaires_en_cours" couleur="emerald" />
        </div>
      </section>

      <section>
        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">À traiter</h2>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
          <StatCard label="Documents en attente" :valeur="stats.documents_en_attente" couleur="amber" />
          <StatCard label="Absences en attente" :valeur="stats.absences_en_attente" couleur="amber" />
          <StatCard label="Rendez-vous à venir" :valeur="stats.rendez_vous_a_venir" couleur="brand" />
        </div>
      </section>

      <section class="grid md:grid-cols-2 gap-4">
        <NuxtLink to="/admin/utilisateurs" class="card p-5 hover:border-brand-300 transition-colors flex items-center justify-between">
          <div>
            <p class="font-semibold text-slate-800">Gérer les comptes</p>
            <p class="text-sm text-slate-500 mt-0.5">Admins, encadrants — création, blocage, suppression</p>
          </div>
          <svg class="w-5 h-5 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </NuxtLink>
        <NuxtLink to="/admin/logs" class="card p-5 hover:border-brand-300 transition-colors flex items-center justify-between">
          <div>
            <p class="font-semibold text-slate-800">Journal d'activité</p>
            <p class="text-sm text-slate-500 mt-0.5">Historique complet des actions de la plateforme</p>
          </div>
          <svg class="w-5 h-5 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </NuxtLink>
      </section>
    </div>
  </div>
</template>
