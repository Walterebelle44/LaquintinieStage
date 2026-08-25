<script setup>
definePageMeta({ layout: 'default' })
const authStore = useAuthStore()

onMounted(() => {
  navigateTo(authStore.estConnecte ? authStore.accueilParRole() : '/connexion')
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center text-slate-400 text-sm">
    Chargement…
  </div>
</template>
