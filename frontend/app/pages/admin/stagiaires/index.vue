<script setup>
definePageMeta({ layout: 'app' })
const authStore = useAuthStore()
const prefixe = computed(() => authStore.estAdmin ? '/admin' : '/encadrant')
</script>

<template>
  <StagiairesListe :prefixe="prefixe" />
</template>
