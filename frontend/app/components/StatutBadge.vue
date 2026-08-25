<script setup>
const props = defineProps({
  statut: { type: String, required: true },
})

const styles = {
  // statuts stagiaire
  en_attente: 'bg-amber-50 text-amber-700',
  en_cours: 'bg-blue-50 text-blue-700',
  termine: 'bg-emerald-50 text-emerald-700',
  abandonne: 'bg-slate-100 text-slate-500',
  // présences
  present: 'bg-emerald-50 text-emerald-700',
  retard: 'bg-amber-50 text-amber-700',
  absent: 'bg-red-50 text-red-700',
  // absences / documents
  approuvee: 'bg-emerald-50 text-emerald-700',
  valide: 'bg-emerald-50 text-emerald-700',
  rejetee: 'bg-red-50 text-red-700',
  rejete: 'bg-red-50 text-red-700',
  // rendez-vous
  planifie: 'bg-blue-50 text-blue-700',
  annule: 'bg-red-50 text-red-700',
  // comptes
  actif: 'bg-emerald-50 text-emerald-700',
  bloque: 'bg-red-50 text-red-700',
  inactif: 'bg-slate-100 text-slate-500',
}

const libelles = {
  en_attente: 'En attente',
  en_cours: 'En cours',
  termine: 'Terminé',
  abandonne: 'Abandonné',
  present: 'Présent',
  retard: 'Retard',
  absent: 'Absent',
  approuvee: 'Approuvée',
  valide: 'Validé',
  rejetee: 'Rejetée',
  rejete: 'Rejeté',
  planifie: 'Planifié',
  annule: 'Annulé',
  actif: 'Actif',
  bloque: 'Bloqué',
  inactif: 'Inactif',
}
</script>

<template>
  <span class="badge" :class="styles[statut] || 'bg-slate-100 text-slate-600'">
    {{ libelles[statut] || statut }}
  </span>
</template>
