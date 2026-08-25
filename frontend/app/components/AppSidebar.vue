<script setup>
const authStore = useAuthStore()
const route = useRoute()

const liensAdmin = [
  { label: 'Tableau de bord', to: '/admin/tableau-de-bord', icon: 'grid' },
  { label: 'Stagiaires', to: '/admin/stagiaires', icon: 'users' },
  { label: 'Comptes utilisateurs', to: '/admin/utilisateurs', icon: 'shield' },
  { label: 'Rendez-vous', to: '/admin/rendez-vous', icon: 'calendar' },
  { label: 'Établissements', to: '/admin/etablissements', icon: 'building' },
  { label: "Journal d'activité", to: '/admin/logs', icon: 'list' },
]

const liensEncadrant = [
  { label: 'Mes stagiaires', to: '/encadrant/stagiaires', icon: 'users' },
  { label: 'Rendez-vous', to: '/encadrant/rendez-vous', icon: 'calendar' },
]

const liensStagiaire = [
  { label: 'Ma feuille de route', to: '/stagiaire/feuille-de-route', icon: 'route' },
  { label: 'Mes présences', to: '/stagiaire/presences', icon: 'clock' },
  { label: 'Mes documents', to: '/stagiaire/documents', icon: 'file' },
]

const liens = computed(() => {
  if (authStore.estAdmin) return liensAdmin
  if (authStore.estEncadrant) return liensEncadrant
  if (authStore.estStagiaire) return liensStagiaire
  return []
})

const estActif = (to) => route.path === to || route.path.startsWith(to + '/')

const icones = {
  grid: 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z',
  users: 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-3a4 4 0 100-8 4 4 0 000 8zM15 14a4 4 0 014 4',
  shield: 'M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z',
  calendar: 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z',
  building: 'M3 21h18M9 8h1m-1 4h1m-1 4h1m4-8h1m-1 4h1m-1 4h1M6 21V5a1 1 0 011-1h10a1 1 0 011 1v16',
  list: 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
  route: 'M4 19a2 2 0 100-4 2 2 0 000 4zM20 5a2 2 0 100-4 2 2 0 000 4zM6 17c8 0 4-12 12-12',
  clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  file: 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM14 2v6h6',
}
</script>

<template>
  <aside class="hidden lg:flex lg:flex-col w-64 shrink-0 border-r border-slate-200 bg-white h-screen sticky top-0">
    <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-100">
      <div class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center text-white font-bold text-sm">HL</div>
      <div class="leading-tight">
        <p class="text-sm font-semibold text-slate-800">Hôpital Laquintinie</p>
        <p class="text-xs text-slate-400">Gestion des stagiaires</p>
      </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <NuxtLink
        v-for="lien in liens"
        :key="lien.to"
        :to="lien.to"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
        :class="estActif(lien.to)
          ? 'bg-brand-50 text-brand-700'
          : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
      >
        <svg class="w-[18px] h-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path :d="icones[lien.icon]" />
        </svg>
        {{ lien.label }}
      </NuxtLink>
    </nav>

    <div class="p-3 border-t border-slate-100">
      <div class="flex items-center gap-3 px-2 py-2">
        <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold">
          {{ authStore.nomComplet?.charAt(0) || '?' }}
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-slate-800 truncate">{{ authStore.nomComplet }}</p>
          <p class="text-xs text-slate-400 capitalize">{{ authStore.role }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>
