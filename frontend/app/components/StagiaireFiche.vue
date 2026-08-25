<script setup>
const props = defineProps({ prefixe: { type: String, required: true } })

const route = useRoute()
const api = useApi()
const authStore = useAuthStore()

const stagiaire = ref(null)
const chargement = ref(true)
const ongletActif = ref('apercu')

const onglets = [
  { id: 'apercu', label: 'Aperçu' },
  { id: 'presences', label: 'Présences' },
  { id: 'absences', label: 'Absences' },
  { id: 'evaluations', label: 'Évaluations' },
  { id: 'documents', label: 'Documents' },
  { id: 'rendez-vous', label: 'Rendez-vous' },
]

const charger = async () => {
  chargement.value = true
  try {
    stagiaire.value = await api.get(`/stagiaires/${route.params.id}`)
  } finally {
    chargement.value = false
  }
}
onMounted(charger)

// ------- Absences -------
const traiterAbsence = async (absence, statut) => {
  await api.put(`/absences/${absence.id}/traiter`, { statut })
  await charger()
}

// ------- Évaluations -------
const modaleEvalOuverte = ref(false)
const evalForm = reactive({ type: 'suivi_mensuel', periode: '', note_globale: '', appreciation: '', points_forts: '', axes_amelioration: '', date_evaluation: '' })
const ouvrirEvaluation = () => {
  Object.assign(evalForm, { type: 'suivi_mensuel', periode: '', note_globale: '', appreciation: '', points_forts: '', axes_amelioration: '', date_evaluation: new Date().toISOString().slice(0, 10) })
  modaleEvalOuverte.value = true
}
const enregistrerEvaluation = async () => {
  await api.post(`/stagiaires/${stagiaire.value.id}/evaluations`, evalForm)
  modaleEvalOuverte.value = false
  await charger()
}

// ------- Documents -------
const traiterDocument = async (doc, statut) => {
  await api.put(`/documents/${doc.id}/traiter`, { statut })
  await charger()
}

// ------- Rendez-vous -------
const modaleRdvOuverte = ref(false)
const rdvForm = reactive({ titre: '', description: '', date_heure: '', lieu: '' })
const ouvrirRdv = () => {
  Object.assign(rdvForm, { titre: '', description: '', date_heure: '', lieu: '' })
  modaleRdvOuverte.value = true
}
const enregistrerRdv = async () => {
  await api.post(`/stagiaires/${stagiaire.value.id}/rendez-vous`, rdvForm)
  modaleRdvOuverte.value = false
  await charger()
}

// ------- Statut du dossier -------
const changerStatut = async (statut) => {
  await api.put(`/stagiaires/${stagiaire.value.id}`, { statut })
  await charger()
}

const formaterDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
const formaterDateHeure = (d) => d ? new Date(d).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' }) : '—'
</script>

<template>
  <div>
    <AppHeader :titre="stagiaire ? `${stagiaire.user.prenom} ${stagiaire.user.nom}` : 'Dossier stagiaire'" />

    <div v-if="chargement" class="text-slate-400 text-sm mt-6">Chargement du dossier…</div>

    <div v-else-if="stagiaire" class="mt-2">
      <div class="card p-5 mb-5 flex flex-wrap items-center gap-4 justify-between">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xl font-semibold">
            {{ stagiaire.user.prenom.charAt(0) }}{{ stagiaire.user.nom.charAt(0) }}
          </div>
          <div>
            <p class="font-semibold text-slate-800 text-lg">{{ stagiaire.user.prenom }} {{ stagiaire.user.nom }}</p>
            <p class="text-sm text-slate-500">{{ stagiaire.matricule }} · {{ stagiaire.user.email }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <StatutBadge :statut="stagiaire.statut" />
          <select :value="stagiaire.statut" class="input-field w-auto text-sm" @change="changerStatut($event.target.value)">
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="termine">Terminé</option>
            <option value="abandonne">Abandonné</option>
          </select>
        </div>
      </div>

      <div class="flex gap-1 border-b border-slate-200 mb-5 overflow-x-auto">
        <button
          v-for="o in onglets" :key="o.id"
          class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap transition-colors"
          :class="ongletActif === o.id ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
          @click="ongletActif = o.id"
        >{{ o.label }}</button>
      </div>

      <!-- APERÇU -->
      <div v-if="ongletActif === 'apercu'" class="grid md:grid-cols-2 gap-4">
        <div class="card p-5">
          <h3 class="font-semibold text-slate-800 mb-3">Informations du stage</h3>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Type de parcours</dt><dd class="font-medium capitalize">{{ stagiaire.type_stage }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Service</dt><dd class="font-medium">{{ stagiaire.service || '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Sujet</dt><dd class="font-medium">{{ stagiaire.sujet || '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Début</dt><dd class="font-medium">{{ formaterDate(stagiaire.date_debut) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Fin</dt><dd class="font-medium">{{ formaterDate(stagiaire.date_fin) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Encadrant</dt><dd class="font-medium">{{ stagiaire.encadrant ? `${stagiaire.encadrant.prenom} ${stagiaire.encadrant.nom}` : '—' }}</dd></div>
          </dl>
        </div>

        <div v-if="stagiaire.type_stage === 'academique'" class="card p-5">
          <h3 class="font-semibold text-slate-800 mb-3">Parcours académique</h3>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Établissement</dt><dd class="font-medium">{{ stagiaire.etablissement?.nom || '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Maître de stage (école)</dt><dd class="font-medium">{{ stagiaire.maitre_stage_ecole_nom || '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Niveau d'étude</dt><dd class="font-medium">{{ stagiaire.niveau_etude || '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Convention signée</dt><dd class="font-medium">{{ stagiaire.convention_signee ? 'Oui' : 'Non' }}</dd></div>
          </dl>
        </div>
        <div v-else class="card p-5">
          <h3 class="font-semibold text-slate-800 mb-3">Parcours professionnel</h3>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Référentiel de compétences</dt><dd class="font-medium text-right">{{ stagiaire.referentiel_competences || '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Employabilité souhaitée</dt><dd class="font-medium">{{ stagiaire.employabilite_souhaitee || '—' }}</dd></div>
          </dl>
        </div>
      </div>

      <!-- PRÉSENCES -->
      <div v-else-if="ongletActif === 'presences'" class="card overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <tr><th class="text-left px-4 py-3">Date</th><th class="text-left px-4 py-3">Arrivée</th><th class="text-left px-4 py-3">Départ</th><th class="text-left px-4 py-3">Statut</th></tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="!stagiaire.presences?.length"><td colspan="4" class="px-4 py-6 text-center text-slate-400">Aucune présence enregistrée.</td></tr>
            <tr v-for="p in stagiaire.presences" :key="p.id">
              <td class="px-4 py-3">{{ formaterDate(p.date) }}</td>
              <td class="px-4 py-3">{{ p.heure_arrivee || '—' }}</td>
              <td class="px-4 py-3">{{ p.heure_depart || '—' }}</td>
              <td class="px-4 py-3"><StatutBadge :statut="p.statut" /></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ABSENCES -->
      <div v-else-if="ongletActif === 'absences'" class="space-y-3">
        <div v-if="!stagiaire.absences?.length" class="card p-6 text-center text-slate-400 text-sm">Aucune demande d'absence.</div>
        <div v-for="a in stagiaire.absences" :key="a.id" class="card p-4 flex items-center justify-between flex-wrap gap-3">
          <div>
            <p class="font-medium text-slate-800">{{ formaterDate(a.date_debut) }} → {{ formaterDate(a.date_fin) }}</p>
            <p class="text-sm text-slate-500">{{ a.motif }}</p>
          </div>
          <div class="flex items-center gap-2">
            <StatutBadge :statut="a.statut" />
            <template v-if="a.statut === 'en_attente'">
              <button class="text-xs px-2.5 py-1.5 rounded-md text-emerald-700 bg-emerald-50 hover:bg-emerald-100" @click="traiterAbsence(a, 'approuvee')">Approuver</button>
              <button class="text-xs px-2.5 py-1.5 rounded-md text-red-600 bg-red-50 hover:bg-red-100" @click="traiterAbsence(a, 'rejetee')">Rejeter</button>
            </template>
          </div>
        </div>
      </div>

      <!-- ÉVALUATIONS -->
      <div v-else-if="ongletActif === 'evaluations'" class="space-y-3">
        <div class="flex justify-end"><button class="btn-primary" @click="ouvrirEvaluation">+ Nouvelle évaluation</button></div>
        <div v-if="!stagiaire.evaluations?.length" class="card p-6 text-center text-slate-400 text-sm">Aucune évaluation enregistrée.</div>
        <div v-for="e in stagiaire.evaluations" :key="e.id" class="card p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="badge bg-brand-50 text-brand-700 capitalize">{{ e.type.replace('_', ' ') }}</span>
            <span class="text-sm text-slate-500">{{ formaterDate(e.date_evaluation) }}</span>
          </div>
          <p v-if="e.note_globale" class="text-sm"><strong>Note :</strong> {{ e.note_globale }}/20</p>
          <p v-if="e.appreciation" class="text-sm text-slate-600 mt-1">{{ e.appreciation }}</p>
        </div>
      </div>

      <!-- DOCUMENTS -->
      <div v-else-if="ongletActif === 'documents'" class="space-y-3">
        <div v-if="!stagiaire.documents?.length" class="card p-6 text-center text-slate-400 text-sm">Aucun document déposé.</div>
        <div v-for="d in stagiaire.documents" :key="d.id" class="card p-4 flex items-center justify-between flex-wrap gap-3">
          <div>
            <p class="font-medium text-slate-800">{{ d.titre }}</p>
            <p class="text-sm text-slate-500 capitalize">{{ d.type }}</p>
          </div>
          <div class="flex items-center gap-2">
            <StatutBadge :statut="d.statut" />
            <a :href="`${useRuntimeConfig().public.apiBase}/documents/${d.id}/telecharger`" target="_blank" class="text-xs px-2.5 py-1.5 rounded-md text-brand-700 bg-brand-50 hover:bg-brand-100">Télécharger</a>
            <template v-if="d.statut === 'en_attente'">
              <button class="text-xs px-2.5 py-1.5 rounded-md text-emerald-700 bg-emerald-50 hover:bg-emerald-100" @click="traiterDocument(d, 'valide')">Valider</button>
              <button class="text-xs px-2.5 py-1.5 rounded-md text-red-600 bg-red-50 hover:bg-red-100" @click="traiterDocument(d, 'rejete')">Rejeter</button>
            </template>
          </div>
        </div>
      </div>

      <!-- RENDEZ-VOUS -->
      <div v-else-if="ongletActif === 'rendez-vous'" class="space-y-3">
        <div class="flex justify-end"><button class="btn-primary" @click="ouvrirRdv">+ Planifier un rendez-vous</button></div>
        <div v-if="!stagiaire.rendez_vous?.length" class="card p-6 text-center text-slate-400 text-sm">Aucun rendez-vous planifié.</div>
        <div v-for="r in stagiaire.rendez_vous" :key="r.id" class="card p-4 flex items-center justify-between flex-wrap gap-3">
          <div>
            <p class="font-medium text-slate-800">{{ r.titre }}</p>
            <p class="text-sm text-slate-500">{{ formaterDateHeure(r.date_heure) }}<span v-if="r.lieu"> · {{ r.lieu }}</span></p>
          </div>
          <StatutBadge :statut="r.statut" />
        </div>
      </div>
    </div>

    <ModaleBase :ouvert="modaleEvalOuverte" titre="Nouvelle évaluation" @fermer="modaleEvalOuverte = false">
      <form class="space-y-4" @submit.prevent="enregistrerEvaluation">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Type d'évaluation</label>
          <select v-model="evalForm.type" class="input-field">
            <option value="suivi_mensuel">Suivi mensuel</option>
            <option value="grille_academique">Grille académique</option>
            <option value="bilan_competences">Bilan de compétences</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Période</label><input v-model="evalForm.periode" placeholder="Mois 1…" class="input-field" /></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Note globale (/20)</label><input v-model="evalForm.note_globale" type="number" min="0" max="20" step="0.5" class="input-field" /></div>
        </div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Date d'évaluation</label><input v-model="evalForm.date_evaluation" type="date" required class="input-field" /></div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Appréciation</label><textarea v-model="evalForm.appreciation" rows="3" class="input-field" /></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Points forts</label><textarea v-model="evalForm.points_forts" rows="2" class="input-field" /></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Axes d'amélioration</label><textarea v-model="evalForm.axes_amelioration" rows="2" class="input-field" /></div>
        </div>
        <button type="submit" class="btn-primary w-full">Enregistrer l'évaluation</button>
      </form>
    </ModaleBase>

    <ModaleBase :ouvert="modaleRdvOuverte" titre="Planifier un rendez-vous" @fermer="modaleRdvOuverte = false">
      <form class="space-y-4" @submit.prevent="enregistrerRdv">
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Titre</label><input v-model="rdvForm.titre" required class="input-field" /></div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Date et heure</label><input v-model="rdvForm.date_heure" type="datetime-local" required class="input-field" /></div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Lieu</label><input v-model="rdvForm.lieu" class="input-field" /></div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label><textarea v-model="rdvForm.description" rows="3" class="input-field" /></div>
        <button type="submit" class="btn-primary w-full">Planifier</button>
      </form>
    </ModaleBase>
  </div>
</template>
