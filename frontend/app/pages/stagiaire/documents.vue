<script setup>
definePageMeta({ layout: 'app' })

const api = useApi()
const donnees = ref(null)
const chargement = ref(true)
const erreurChargement = ref('')

const modaleOuverte = ref(false)
const enregistrement = ref(false)
const erreur = ref('')
const formulaire = reactive({ type: 'rapport', titre: '', fichier: null })

const charger = async () => {
  chargement.value = true
  erreurChargement.value = ''
  try {
    donnees.value = await api.get('/mon-espace/feuille-de-route')
  } catch (e) {
    erreurChargement.value = e?.data?.message || "Impossible de charger vos documents pour le moment."
  } finally {
    chargement.value = false
  }
}
onMounted(charger)

const ouvrirDepot = () => {
  Object.assign(formulaire, { type: 'rapport', titre: '', fichier: null })
  erreur.value = ''
  modaleOuverte.value = true
}

const gererFichier = (e) => {
  formulaire.fichier = e.target.files[0] || null
}

const deposer = async () => {
  if (!formulaire.fichier) {
    erreur.value = 'Veuillez sélectionner un fichier.'
    return
  }
  erreur.value = ''
  enregistrement.value = true
  try {
    const fd = new FormData()
    fd.append('type', formulaire.type)
    fd.append('titre', formulaire.titre)
    fd.append('fichier', formulaire.fichier)
    await api.post('/mon-espace/documents', fd)
    modaleOuverte.value = false
    await charger()
  } catch (e) {
    erreur.value = e?.data?.message || Object.values(e?.data?.errors || {})[0]?.[0] || 'Erreur lors du dépôt du document.'
  } finally {
    enregistrement.value = false
  }
}
</script>

<template>
  <div>
    <AppHeader titre="Mes documents" />

    <div class="flex justify-end mt-2 mb-5">
      <button class="btn-primary" @click="ouvrirDepot">+ Déposer un document</button>
    </div>

    <div v-if="chargement" class="text-slate-400 text-sm">Chargement…</div>

    <div v-else-if="erreurChargement" class="card p-6 text-center text-sm">
      <p class="text-red-600">{{ erreurChargement }}</p>
      <button class="btn-secondary mt-3" @click="charger">Réessayer</button>
    </div>

    <div v-else-if="donnees" class="space-y-3">
      <div v-if="!donnees.documents?.length" class="card p-8 text-center text-slate-400 text-sm">
        Vous n'avez déposé aucun document pour le moment.
      </div>
      <div v-for="d in donnees.documents" :key="d.id" class="card p-4 flex items-center justify-between flex-wrap gap-3">
        <div>
          <p class="font-medium text-slate-800">{{ d.titre }}</p>
          <p class="text-sm text-slate-500 capitalize">{{ d.type }}</p>
          <p v-if="d.commentaire" class="text-xs text-slate-400 mt-1">Commentaire : {{ d.commentaire }}</p>
        </div>
        <StatutBadge :statut="d.statut" />
      </div>
    </div>

    <ModaleBase :ouvert="modaleOuverte" titre="Déposer un document" @fermer="modaleOuverte = false">
      <form class="space-y-4" @submit.prevent="deposer">
        <div v-if="erreur" class="px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm">{{ erreur }}</div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Type de document</label>
          <select v-model="formulaire.type" class="input-field">
            <option value="convention">Convention de stage</option>
            <option value="rapport">Rapport</option>
            <option value="memoire">Mémoire</option>
            <option value="livrable">Livrable</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Titre du document</label>
          <input v-model="formulaire.titre" required class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Fichier (PDF, Word ou image — 20 Mo max)</label>
          <input type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required class="input-field" @change="gererFichier" />
        </div>
        <button type="submit" :disabled="enregistrement" class="btn-primary w-full">
          {{ enregistrement ? 'Envoi…' : 'Déposer le document' }}
        </button>
      </form>
    </ModaleBase>
  </div>
</template>