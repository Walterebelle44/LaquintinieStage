<script setup>
definePageMeta({ layout: 'app' })

const api = useApi()
const etablissements = ref([])
const chargement = ref(true)
const modaleOuverte = ref(false)
const enregistrement = ref(false)

const formulaire = reactive({ nom: '', ville: '', filiere: '', contact_nom: '', contact_email: '', contact_telephone: '' })

const charger = async () => {
  chargement.value = true
  try {
    etablissements.value = await api.get('/etablissements')
  } finally {
    chargement.value = false
  }
}
onMounted(charger)

const ouvrirCreation = () => {
  Object.assign(formulaire, { nom: '', ville: '', filiere: '', contact_nom: '', contact_email: '', contact_telephone: '' })
  modaleOuverte.value = true
}

const enregistrer = async () => {
  enregistrement.value = true
  try {
    await api.post('/admin/etablissements', formulaire)
    modaleOuverte.value = false
    await charger()
  } finally {
    enregistrement.value = false
  }
}

const supprimer = async (etab) => {
  if (!confirm(`Supprimer « ${etab.nom} » ?`)) return
  await api.del(`/admin/etablissements/${etab.id}`)
  await charger()
}
</script>

<template>
  <div>
    <AppHeader titre="Établissements partenaires" />

    <div class="flex justify-end mt-2 mb-5">
      <button class="btn-primary" @click="ouvrirCreation">+ Nouvel établissement</button>
    </div>

    <div v-if="chargement" class="text-slate-400 text-sm">Chargement…</div>
    <div v-else-if="!etablissements.length" class="card p-8 text-center text-slate-400 text-sm">
      Aucun établissement enregistré pour le moment.
    </div>
    <div v-else class="grid md:grid-cols-2 gap-4">
      <div v-for="etab in etablissements" :key="etab.id" class="card p-5">
        <div class="flex items-start justify-between">
          <div>
            <p class="font-semibold text-slate-800">{{ etab.nom }}</p>
            <p class="text-sm text-slate-500">{{ etab.ville }} · {{ etab.filiere }}</p>
          </div>
          <button class="text-xs px-2.5 py-1.5 rounded-md text-red-600 bg-red-50 hover:bg-red-100" @click="supprimer(etab)">Supprimer</button>
        </div>
        <div v-if="etab.contact_nom" class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-500">
          Contact : {{ etab.contact_nom }} · {{ etab.contact_email }}
        </div>
      </div>
    </div>

    <ModaleBase :ouvert="modaleOuverte" titre="Nouvel établissement" @fermer="modaleOuverte = false">
      <form class="space-y-4" @submit.prevent="enregistrer">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom de l'établissement</label>
          <input v-model="formulaire.nom" required class="input-field" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Ville</label>
            <input v-model="formulaire.ville" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Filière</label>
            <input v-model="formulaire.filiere" class="input-field" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom du contact</label>
          <input v-model="formulaire.contact_nom" class="input-field" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">E-mail du contact</label>
            <input v-model="formulaire.contact_email" type="email" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Téléphone</label>
            <input v-model="formulaire.contact_telephone" class="input-field" />
          </div>
        </div>
        <button type="submit" :disabled="enregistrement" class="btn-primary w-full">
          {{ enregistrement ? 'Enregistrement…' : 'Enregistrer' }}
        </button>
      </form>
    </ModaleBase>
  </div>
</template>
