<script setup>
definePageMeta({ layout: 'app' })

const api = useApi()
const utilisateurs = ref([])
const chargement = ref(true)
const filtreRole = ref('')
const recherche = ref('')

const modaleOuverte = ref(false)
const modaleBlocageOuverte = ref(false)
const cible = ref(null)
const erreur = ref('')
const enregistrement = ref(false)

const formulaire = reactive({
  nom: '', prenom: '', email: '', telephone: '', role: 'encadrant', password: '',
})
const motifBlocage = ref('')

const charger = async () => {
  chargement.value = true
  try {
    const params = {}
    if (filtreRole.value) params.role = filtreRole.value
    if (recherche.value) params.recherche = recherche.value
    const res = await api.get('/admin/utilisateurs', params)
    utilisateurs.value = res.data
  } finally {
    chargement.value = false
  }
}

onMounted(charger)
watch([filtreRole, recherche], () => charger())

const ouvrirCreation = () => {
  Object.assign(formulaire, { nom: '', prenom: '', email: '', telephone: '', role: 'encadrant', password: '' })
  erreur.value = ''
  modaleOuverte.value = true
}

const creerCompte = async () => {
  erreur.value = ''
  enregistrement.value = true
  try {
    await api.post('/admin/utilisateurs', formulaire)
    modaleOuverte.value = false
    await charger()
  } catch (e) {
    erreur.value = e?.data?.message || Object.values(e?.data?.errors || {})[0]?.[0] || 'Erreur lors de la création.'
  } finally {
    enregistrement.value = false
  }
}

const ouvrirBlocage = (user) => {
  cible.value = user
  motifBlocage.value = ''
  modaleBlocageOuverte.value = true
}

const confirmerBlocage = async () => {
  if (!motifBlocage.value.trim()) return
  await api.put(`/admin/utilisateurs/${cible.value.id}/bloquer`, { motif: motifBlocage.value })
  modaleBlocageOuverte.value = false
  await charger()
}

const debloquer = async (user) => {
  await api.put(`/admin/utilisateurs/${user.id}/debloquer`)
  await charger()
}

const basculerActivation = async (user) => {
  if (user.is_active) {
    await api.put(`/admin/utilisateurs/${user.id}/restreindre`)
  } else {
    await api.put(`/admin/utilisateurs/${user.id}/reactiver`)
  }
  await charger()
}

const supprimer = async (user) => {
  if (!confirm(`Supprimer définitivement le compte de ${user.nom_complet} ?`)) return
  await api.del(`/admin/utilisateurs/${user.id}`)
  await charger()
}

const statutDe = (user) => {
  if (user.is_blocked) return 'bloque'
  if (!user.is_active) return 'inactif'
  return 'actif'
}
</script>

<template>
  <div>
    <AppHeader titre="Gestion des comptes" />

    <div class="mt-2 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between mb-5">
      <div class="flex flex-wrap gap-2">
        <select v-model="filtreRole" class="input-field w-auto">
          <option value="">Tous les rôles</option>
          <option value="admin">Administrateurs</option>
          <option value="encadrant">Encadrants</option>
          <option value="stagiaire">Stagiaires</option>
        </select>
        <input v-model="recherche" type="text" placeholder="Rechercher un nom, e-mail…" class="input-field w-56" />
      </div>
      <button class="btn-primary" @click="ouvrirCreation">+ Nouveau compte</button>
    </div>

    <div class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Nom</th>
            <th class="text-left px-4 py-3 font-medium">E-mail</th>
            <th class="text-left px-4 py-3 font-medium">Rôle</th>
            <th class="text-left px-4 py-3 font-medium">Statut</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="chargement"><td colspan="5" class="px-4 py-6 text-center text-slate-400">Chargement…</td></tr>
          <tr v-else-if="!utilisateurs.length"><td colspan="5" class="px-4 py-6 text-center text-slate-400">Aucun compte trouvé.</td></tr>
          <tr v-for="user in utilisateurs" :key="user.id" class="hover:bg-slate-50/60">
            <td class="px-4 py-3 font-medium text-slate-800">{{ user.nom_complet }}</td>
            <td class="px-4 py-3 text-slate-500">{{ user.email }}</td>
            <td class="px-4 py-3 capitalize text-slate-600">{{ user.role }}</td>
            <td class="px-4 py-3"><StatutBadge :statut="statutDe(user)" /></td>
            <td class="px-4 py-3">
              <div class="flex justify-end gap-1.5 flex-wrap">
                <button
                  v-if="!user.is_blocked"
                  class="text-xs px-2.5 py-1.5 rounded-md text-amber-700 bg-amber-50 hover:bg-amber-100"
                  @click="ouvrirBlocage(user)"
                >Bloquer</button>
                <button
                  v-else
                  class="text-xs px-2.5 py-1.5 rounded-md text-emerald-700 bg-emerald-50 hover:bg-emerald-100"
                  @click="debloquer(user)"
                >Débloquer</button>
                <button
                  class="text-xs px-2.5 py-1.5 rounded-md text-slate-600 bg-slate-100 hover:bg-slate-200"
                  @click="basculerActivation(user)"
                >{{ user.is_active ? 'Restreindre' : 'Réactiver' }}</button>
                <button
                  class="text-xs px-2.5 py-1.5 rounded-md text-red-600 bg-red-50 hover:bg-red-100"
                  @click="supprimer(user)"
                >Supprimer</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ModaleBase :ouvert="modaleOuverte" titre="Créer un compte" @fermer="modaleOuverte = false">
      <form class="space-y-4" @submit.prevent="creerCompte">
        <div v-if="erreur" class="px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm">{{ erreur }}</div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Prénom</label>
            <input v-model="formulaire.prenom" required class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom</label>
            <input v-model="formulaire.nom" required class="input-field" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">E-mail</label>
          <input v-model="formulaire.email" type="email" required class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Téléphone</label>
          <input v-model="formulaire.telephone" class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Rôle</label>
          <select v-model="formulaire.role" class="input-field">
            <option value="encadrant">Encadrant</option>
            <option value="admin">Administrateur</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe</label>
          <input v-model="formulaire.password" type="password" required minlength="8" class="input-field" />
        </div>
        <button type="submit" :disabled="enregistrement" class="btn-primary w-full">
          {{ enregistrement ? 'Création…' : 'Créer le compte' }}
        </button>
      </form>
    </ModaleBase>

    <ModaleBase :ouvert="modaleBlocageOuverte" titre="Bloquer ce compte" @fermer="modaleBlocageOuverte = false">
      <div class="space-y-4">
        <p class="text-sm text-slate-600">
          Vous êtes sur le point de bloquer le compte de <strong>{{ cible?.nom_complet }}</strong>.
          Veuillez indiquer le motif du blocage.
        </p>
        <textarea v-model="motifBlocage" rows="3" class="input-field" placeholder="Motif du blocage…" />
        <div class="flex gap-2">
          <button class="btn-secondary flex-1" @click="modaleBlocageOuverte = false">Annuler</button>
          <button class="btn-primary flex-1" :disabled="!motifBlocage.trim()" @click="confirmerBlocage">Confirmer le blocage</button>
        </div>
      </div>
    </ModaleBase>
  </div>
</template>
