<script setup>
const props = defineProps({ error: Object })

const revenir = () => clearError({ redirect: '/' })
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 p-6">
    <div class="text-center">
      <p class="text-6xl font-bold text-brand-600">{{ error?.statusCode || 404 }}</p>
      <p class="mt-3 text-slate-600">{{ error?.statusMessage || "Une erreur est survenue." }}</p>
      <button class="btn-primary mt-6" @click="revenir">Retour à l'accueil</button>
    </div>
  </div>
</template>
