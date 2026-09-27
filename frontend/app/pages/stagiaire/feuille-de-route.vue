<script setup>
definePageMeta({ layout: 'app' })

const api = useApi()
const donnees = ref(null)
const chargement = ref(true)
const erreurChargement = ref('')

const modaleAbsenceOuverte = ref(false)
const absenceForm = reactive({ date_debut: '', date_fin: '', motif: '' })
const enregistrement = ref(false)
const erreurAbsence = ref('')
const pointageEnCours = ref(false)
const erreurPointage = ref('')

const charger = async () => {
  chargement.value = true
  erreurChargement.value = ''
  try {
    donnees.value = await api.get('/mon-espace/feuille-de-route')
  } catch (e) {
    erreurChargement.value = e?.data?.message || "Impossible de charger votre feuille de route pour le moment."
  } finally {
    chargement.value = false
  }
}
onMounted(charger)

// Date locale au format AAAA-MM-JJ (toISOString() donnerait la date en UTC,
// ce qui peut décaler le jour selon l'heure et le fuseau du navigateur).
const dateLocale = (d = new Date()) => {
  const annee = d.getFullYear()
  const mois = String(d.getMonth() + 1).padStart(2, '0')
  const jour = String(d.getDate()).padStart(2, '0')
  return `${annee}-${mois}-${jour}`
}

const presenceAujourdhui = computed(() => {
  const auj = dateLocale()
  return donnees.value?.presences?.find((p) => p.date === auj)
})

const pointerArrivee = async () => {
  erreurPointage.value = ''
  pointageEnCours.value = true
  try {
    await api.post('/mon-espace/pointage/arrivee')
    await charger()
  } catch (e) {
    erreurPointage.value = e?.data?.message || "Le pointage d'arrivée a échoué. Réessayez."
  } finally {
    pointageEnCours.value = false
  }
}

const pointerDepart = async () => {
  erreurPointage.value = ''
  pointageEnCours.value = true
  try {
    await api.post('/mon-espace/pointage/depart')
    await charger()
  } catch (e) {
    erreurPointage.value = e?.data?.message || "Le pointage de départ a échoué. Réessayez."
  } finally {
    pointageEnCours.value = false
  }
}

const ouvrirDemandeAbsence = () => {
  Object.assign(absenceForm, { date_debut: '', date_fin: '', motif: '' })
  erreurAbsence.value = ''
  modaleAbsenceOuverte.value = true
}

const soumettreAbsence = async () => {
  erreurAbsence.value = ''
  enregistrement.value = true
  try {
    await api.post('/mon-espace/absences', absenceForm)
    modaleAbsenceOuverte.value = false
    await charger()
  } catch (e) {
    erreurAbsence.value = e?.data?.message || Object.values(e?.data?.errors || {})[0]?.[0] || "L'envoi de la demande a échoué. Réessayez."
  } finally {
    enregistrement.value = false
  }
}

const formaterDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
const formaterDateHeure = (d) => d ? new Date(d).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' }) : '—'
</script>

<template>
  <div>
    <AppHeader titre="Ma feuille de route" />

    <div v-if="chargement" class="text-slate-400 text-sm mt-6">Chargement…</div>

    <div v-else-if="erreurChargement" class="card p-6 text-center text-sm mt-2">
      <p class="text-red-600">{{ erreurChargement }}</p>
      <button class="btn-secondary mt-3" @click="charger">Réessayer</button>
    </div>

    <div v-else-if="donnees" class="mt-2 space-y-6">
      <!-- Pointage du jour -->
      <div class="card p-5 flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="font-semibold text-slate-800">Pointage du jour</p>
          <p class="text-sm text-slate-500 mt-0.5">
            <template v-if="presenceAujourdhui">
              Arrivée : {{ presenceAujourdhui.heure_arrivee || '—' }}
              <span v-if="presenceAujourdhui.heure_depart"> · Départ : {{ presenceAujourdhui.heure_depart }}</span>
            </template>
            <template v-else>Vous n'avez pas encore pointé aujourd'hui.</template>
          </p>
        </div>
        <div class="flex gap-2">
          <button v-if="!presenceAujourdhui?.heure_arrivee" class="btn-primary" :disabled="pointageEnCours" @click="pointerArrivee">Pointer l'arrivée</button>
          <button v-else-if="!presenceAujourdhui?.heure_depart" class="btn-secondary" :disabled="pointageEnCours" @click="pointerDepart">Pointer le départ</button>
          <span v-else class="badge bg-emerald-50 text-emerald-700">Journée complète</span>
        </div>
      </div>
      <p v-if="erreurPointage" class="text-sm text-red-600 -mt-4">{{ erreurPointage }}</p>

      <!-- Infos stage -->
      <div class="grid md:grid-cols-2 gap-4">
        <div class="card p-5">
          <h3 class="font-semibold text-slate-800 mb-3">Mon stage</h3>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Type de parcours</dt><dd class="font-medium capitalize">{{ donnees.type_stage }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Statut</dt><dd><StatutBadge :statut="donnees.statut" /></dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Service</dt><dd class="font-medium">{{ donnees.service || '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Du</dt><dd class="font-medium">{{ formaterDate(donnees.date_debut) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Au</dt><dd class="font-medium">{{ formaterDate(donnees.date_fin) }}</dd></div>
          </dl>
        </div>
        <div class="card p-5">
          <h3 class="font-semibold text-slate-800 mb-3">Mon encadrant</h3>
          <div v-if="donnees.encadrant">
            <p class="font-medium text-slate-800">{{ donnees.encadrant.prenom }} {{ donnees.encadrant.nom }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ donnees.encadrant.email }}</p>
            <p v-if="donnees.encadrant.telephone" class="text-sm text-slate-500">{{ donnees.encadrant.telephone }}</p>
          </div>
          <p v-else class="text-sm text-slate-400">Aucun encadrant assigné pour le moment.</p>
        </div>
      </div>

      <!-- Prochains rendez-vous -->
      <div class="card p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Prochains rendez-vous</h3>
        <div v-if="!donnees.rendez_vous?.length" class="text-sm text-slate-400">Aucun rendez-vous à venir.</div>
        <div v-for="r in donnees.rendez_vous" :key="r.id" class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
          <div>
            <p class="text-sm font-medium text-slate-800">{{ r.titre }}</p>
            <p class="text-xs text-slate-500">{{ formaterDateHeure(r.date_heure) }}<span v-if="r.lieu"> · {{ r.lieu }}</span></p>
          </div>
          <StatutBadge :statut="r.statut" />
        </div>
      </div>

      <!-- Absences -->
      <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-slate-800">Mes demandes d'absence</h3>
          <button class="btn-secondary text-sm" @click="ouvrirDemandeAbsence">+ Nouvelle demande</button>
        </div>
        <div v-if="!donnees.absences?.length" class="text-sm text-slate-400">Aucune demande déposée.</div>
        <div v-for="a in donnees.absences" :key="a.id" class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
          <div>
            <p class="text-sm font-medium text-slate-800">{{ formaterDate(a.date_debut) }} → {{ formaterDate(a.date_fin) }}</p>
            <p class="text-xs text-slate-500">{{ a.motif }}</p>
          </div>
          <StatutBadge :statut="a.statut" />
        </div>
      </div>

      <!-- Évaluations validées -->
      <div class="card p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Mes évaluations</h3>
        <div v-if="!donnees.evaluations?.length" class="text-sm text-slate-400">Aucune évaluation disponible pour le moment.</div>
        <div v-for="e in donnees.evaluations" :key="e.id" class="py-2 border-b border-slate-50 last:border-0">
          <div class="flex items-center justify-between">
            <span class="badge bg-brand-50 text-brand-700 capitalize">{{ e.type.replace('_', ' ') }}</span>
            <span v-if="e.note_globale" class="text-sm font-semibold text-slate-700">{{ e.note_globale }}/20</span>
          </div>
          <p v-if="e.appreciation" class="text-sm text-slate-600 mt-1">{{ e.appreciation }}</p>
        </div>
      </div>
    </div>

    <ModaleBase :ouvert="modaleAbsenceOuverte" titre="Demande d'absence" @fermer="modaleAbsenceOuverte = false">
      <form class="space-y-4" @submit.prevent="soumettreAbsence">
        <div v-if="erreurAbsence" class="px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm">{{ erreurAbsence }}</div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Du</label><input v-model="absenceForm.date_debut" type="date" required class="input-field" /></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Au</label><input v-model="absenceForm.date_fin" type="date" required class="input-field" /></div>
        </div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Motif</label><textarea v-model="absenceForm.motif" rows="3" required class="input-field" /></div>
        <button type="submit" :disabled="enregistrement" class="btn-primary w-full">{{ enregistrement ? 'Envoi…' : 'Envoyer la demande' }}</button>
      </form>
    </ModaleBase>
  </div>
</template>