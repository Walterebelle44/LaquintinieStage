<script setup>
defineProps({
  label: { type: String, required: true },
  valeur: { type: [String, Number], required: true },
  couleur: { type: String, default: 'brand' }, // brand | emerald | amber | red
})

const couleurs = {
  brand: 'bg-brand-50 text-brand-700',
  emerald: 'bg-emerald-50 text-emerald-700',
  amber: 'bg-amber-50 text-amber-700',
  red: 'bg-red-50 text-red-700',
}
</script>

<template>
  <div class="card p-5">
    <p class="text-sm text-slate-500 font-medium">{{ label }}</p>
    <div class="mt-2 flex items-end justify-between">
      <p class="text-3xl font-bold text-slate-800">{{ valeur }}</p>
      <span class="w-9 h-9 rounded-lg flex items-center justify-center" :class="couleurs[couleur]">
        <slot name="icone" />
      </span>
    </div>
  </div>
</template>
