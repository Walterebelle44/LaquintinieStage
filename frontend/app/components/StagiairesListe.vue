<script setup>
const props = defineProps({
  prefixe: { type: String, required: true }, // '/admin' ou '/encadrant'
})

const api = useApi()
const authStore = useAuthStore()

const stagiaires = ref([])
const encadrants = ref([])
const etablissements = ref([])
const chargement = ref(true)
const filtreType = ref('')
const filtreStatut = ref('')
const recherche = ref('')

const modaleOuverte = ref(false)
const enregistrement = ref(false)
const erreur = ref('')

const formulaire = reactive({
  nom: '', prenom: '', email: '', telephone: '', password: '',
  type_stage: 'academique', encadrant_id: '', etablissement_id: '',
  maitre_stage_ecole_nom: '', maitre_stage_ecole_email: '', niveau_etude: '',
  referentiel_competences: '', employabilite_souhaitee: '',
  service: '', sujet: '', date_debut: '', date_fin: '',
})

const charger = async () => {
  chargement.value = true
  try {
    const params = {}
    if (filtreType.value) params.type_stage = filtreType.value
    if (filtreStatut.value) params.statut = filtreStatut.value
    if (recherche.value) params.recherche = recherche.value
    const res = await api.get('/stagiaires', params)
    stagiaires.value = res.data
  } finally {
    chargement.value = false
  }
}

onMounted(async () => {
  await charger()
  if (authStore.estAdmin) {
    const users = await api.get('/admin/utilisateurs', { role: 'encadrant', per_page: 100 })
    encadrants.value = users.data
  }
  etablissements.value = await api.get('/etablissements')
})

watch([filtreType, filtreStatut, recherche], () => charger())

const ouvrirCreation = () => {
  Object.assign(formulaire, {
    nom: '', prenom: '', email: '', telephone: '', password: '',
    type_stage: 'academique', encadrant_id: '', etablissement_id: '',
    maitre_stage_ecole_nom: '', maitre_stage_ecole_email: '', niveau_etude: '',
    referentiel_competences: '', employabilite_souhaitee: '',
    service: '', sujet: '', date_debut: '', date_fin: '',
  })
  erreur.value = ''
  modaleOuverte.value = true
}

const creer = async () => {
  erreur.value = ''
  enregistrement.value = true
  try {
    const payload = { ...formulaire }
    if (!payload.encadrant_id) delete payload.encadrant_id
    if (!payload.etablissement_id) delete payload.etablissement_id
    if (!payload.password) delete payload.password
    await api.post('/stagiaires', payload)
    modaleOuverte.value = false
    await charger()
  } catch (e) {
    erreur.value = e?.data?.message || Object.values(e?.data?.errors || {})[0]?.[0] || 'Erreur lors de la création.'
  } finally {
    enregistrement.value = false
  }
}
</script>

<template>
  <div>
    <AppHeader :titre="prefixe === '/admin' ? 'Tous les stagiaires' : 'Mes stagiaires'" />

    <div class="mt-2 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between mb-5">
      <div class="flex flex-wrap gap-2">
        <select v-model="filtreType" class="input-field w-auto">
          <option value="">Tous les parcours</option>
          <option value="academique">Académique</option>
          <option value="professionnel">Professionnel</option>
        </select>
        <select v-model="filtreStatut" class="input-field w-auto">
          <option value="">Tous les statuts</option>
          <option value="en_attente">En attente</option>
          <option value="en_cours">En cours</option>
          <option value="termine">Terminé</option>
          <option value="abandonne">Abandonné</option>
        </select>
        <input v-model="recherche" type="text" placeholder="Rechercher…" class="input-field w-48" />
      </div>
      <button class="btn-primary" @click="ouvrirCreation">+ Nouveau stagiaire</button>
    </div>

    <div class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Stagiaire</th>
            <th class="text-left px-4 py-3 font-medium">Matricule</th>
            <th class="text-left px-4 py-3 font-medium">Parcours</th>
            <th class="text-left px-4 py-3 font-medium">Encadrant</th>
            <th class="text-left px-4 py-3 font-medium">Statut</th>
            <th class="text-right px-4 py-3 font-medium">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="chargement"><td colspan="6" class="px-4 py-6 text-center text-slate-400">Chargement…</td></tr>
          <tr v-else-if="!stagiaires.length"><td colspan="6" class="px-4 py-6 text-center text-slate-400">Aucun stagiaire trouvé.</td></tr>
          <tr v-for="s in stagiaires" :key="s.id" class="hover:bg-slate-50/60">
            <td class="px-4 py-3 font-medium text-slate-800">{{ s.user?.prenom }} {{ s.user?.nom }}</td>
            <td class="px-4 py-3 text-slate-500">{{ s.matricule }}</td>
            <td class="px-4 py-3 text-slate-600 capitalize">{{ s.type_stage }}</td>
            <td class="px-4 py-3 text-slate-500">{{ s.encadrant ? `${s.encadrant.prenom} ${s.encadrant.nom}` : '—' }}</td>
            <td class="px-4 py-3"><StatutBadge :statut="s.statut" /></td>
            <td class="px-4 py-3 text-right">
              <NuxtLink :to="`${prefixe}/stagiaires/${s.id}`" class="text-brand-600 hover:text-brand-800 text-sm font-medium">
                Voir le dossier →
              </NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ModaleBase :ouvert="modaleOuverte" titre="Nouveau stagiaire" largeur="max-w-2xl" @fermer="modaleOuverte = false">
      <form class="space-y-4" @submit.prevent="creer">
        <div v-if="erreur" class="px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm">{{ erreur }}</div>

        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Informations personnelles</h3>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Prénom</label><input v-model="formulaire.prenom" required class="input-field" /></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nom</label><input v-model="formulaire.nom" required class="input-field" /></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">E-mail</label><input v-model="formulaire.email" type="email" required class="input-field" /></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Téléphone</label><input v-model="formulaire.telephone" class="input-field" /></div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe (optionnel — généré si vide)</label>
          <input v-model="formulaire.password" type="password" class="input-field" />
        </div>

        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide pt-2">Stage</h3>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Type de parcours</label>
            <select v-model="formulaire.type_stage" class="input-field">
              <option value="academique">Académique</option>
              <option value="professionnel">Professionnel</option>
            </select>
          </div>
          <div v-if="authStore.estAdmin">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Encadrant</label>
            <select v-model="formulaire.encadrant_id" class="input-field">
              <option value="">— Non assigné —</option>
              <option v-for="e in encadrants" :key="e.id" :value="e.id">{{ e.prenom }} {{ e.nom }}</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Service</label><input v-model="formulaire.service" class="input-field" /></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Sujet du stage</label><input v-model="formulaire.sujet" class="input-field" /></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Date de début</label><input v-model="formulaire.date_debut" type="date" required class="input-field" /></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Date de fin</label><input v-model="formulaire.date_fin" type="date" required class="input-field" /></div>
        </div>

        <template v-if="formulaire.type_stage === 'academique'">
          <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide pt-2">Parcours académique</h3>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Établissement partenaire</label>
            <select v-model="formulaire.etablissement_id" class="input-field">
              <option value="">— Sélectionner —</option>
              <option v-for="et in etablissements" :key="et.id" :value="et.id">{{ et.nom }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Maître de stage (école)</label><input v-model="formulaire.maitre_stage_ecole_nom" class="input-field" /></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Niveau d'étude</label><input v-model="formulaire.niveau_etude" class="input-field" /></div>
          </div>
        </template>

        <template v-else>
          <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide pt-2">Parcours professionnel</h3>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Référentiel de compétences visé</label><textarea v-model="formulaire.referentiel_competences" rows="2" class="input-field" /></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Employabilité souhaitée</label><input v-model="formulaire.employabilite_souhaitee" class="input-field" /></div>
        </template>

        <button type="submit" :disabled="enregistrement" class="btn-primary w-full">
          {{ enregistrement ? 'Création…' : 'Créer le dossier' }}
        </button>
      </form>
    </ModaleBase>
  </div>
</template>
