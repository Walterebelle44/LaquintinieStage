<script setup>
defineProps({
  ouvert: { type: Boolean, default: false },
  titre: { type: String, default: '' },
  largeur: { type: String, default: 'max-w-lg' },
})
const emit = defineEmits(['fermer'])
</script>

<template>
  <Teleport to="body">
    <div v-if="ouvert" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="emit('fermer')" />
      <div class="relative card w-full p-6 max-h-[90vh] overflow-y-auto shadow-xl" :class="largeur">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-semibold text-slate-800">{{ titre }}</h2>
          <button class="text-slate-400 hover:text-slate-700 p-1" @click="emit('fermer')">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </button>
        </div>
        <slot />
      </div>
    </div>
  </Teleport>
</template>
