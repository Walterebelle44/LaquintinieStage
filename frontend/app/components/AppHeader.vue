<script setup>
const props = defineProps({ titre: { type: String, default: '' } })
const authStore = useAuthStore()
const api = useApi()
const menuOuvert = ref(false)

const seDeconnecter = async () => {
  try {
    await api.post('/logout')
  } catch {
    // on déconnecte localement même si l'appel échoue
  }
  authStore.clear()
  navigateTo('/connexion')
}
</script>

<template>
  <header class="h-16 border-b border-slate-200 bg-white/80 backdrop-blur sticky top-0 z-20 flex items-center justify-between px-4 lg:px-6">
    <div class="flex items-center gap-3">
      <div class="lg:hidden w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white font-bold text-xs">HL</div>
      <h1 class="text-lg font-semibold text-slate-800">{{ titre }}</h1>
    </div>

    <div class="relative">
      <button
        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors"
        @click="menuOuvert = !menuOuvert"
      >
        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold lg:hidden">
          {{ authStore.nomComplet?.charAt(0) || '?' }}
        </div>
        <span class="hidden sm:block text-sm font-medium text-slate-700">{{ authStore.nomComplet }}</span>
        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M6 9l6 6 6-6" />
        </svg>
      </button>

      <div
        v-if="menuOuvert"
        class="absolute right-0 mt-2 w-48 card p-1.5 shadow-lg"
        @click.outside="menuOuvert = false"
      >
        <NuxtLink to="/mon-compte" class="block px-3 py-2 text-sm rounded-lg text-slate-600 hover:bg-slate-50" @click="menuOuvert = false">
          Mon compte
        </NuxtLink>
        <button class="w-full text-left px-3 py-2 text-sm rounded-lg text-red-600 hover:bg-red-50" @click="seDeconnecter">
          Se déconnecter
        </button>
      </div>
    </div>
  </header>
</template>
