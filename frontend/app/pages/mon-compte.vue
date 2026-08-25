<script setup>
definePageMeta({ layout: 'app' })

const api = useApi()
const authStore = useAuthStore()

const formulaire = reactive({ current_password: '', password: '', password_confirmation: '' })
const enregistrement = ref(false)
const message = ref('')
const erreur = ref('')

const enregistrer = async () => {
  message.value = ''
  erreur.value = ''
  enregistrement.value = true
  try {
    await api.put('/mon-compte/mot-de-passe', formulaire)
    message.value = 'Mot de passe mis à jour avec succès.'
    Object.assign(formulaire, { current_password: '', password: '', password_confirmation: '' })
  } catch (e) {
    erreur.value = e?.data?.message || Object.values(e?.data?.errors || {})[0]?.[0] || 'Erreur lors de la mise à jour.'
  } finally {
    enregistrement.value = false
  }
}
</script>

<template>
  <div>
    <AppHeader titre="Mon compte" />

    <div class="mt-2 max-w-lg space-y-5">
      <div class="card p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Informations du compte</h3>
        <dl class="space-y-2 text-sm">
          <div class="flex justify-between"><dt class="text-slate-500">Nom complet</dt><dd class="font-medium">{{ authStore.nomComplet }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">E-mail</dt><dd class="font-medium">{{ authStore.user?.email }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Rôle</dt><dd class="font-medium capitalize">{{ authStore.role }}</dd></div>
        </dl>
      </div>

      <div class="card p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Changer le mot de passe</h3>
        <form class="space-y-4" @submit.prevent="enregistrer">
          <div v-if="message" class="px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ message }}</div>
          <div v-if="erreur" class="px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm">{{ erreur }}</div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe actuel</label>
            <input v-model="formulaire.current_password" type="password" required class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nouveau mot de passe</label>
            <input v-model="formulaire.password" type="password" required minlength="8" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirmer le nouveau mot de passe</label>
            <input v-model="formulaire.password_confirmation" type="password" required class="input-field" />
          </div>
          <button type="submit" :disabled="enregistrement" class="btn-primary w-full">
            {{ enregistrement ? 'Mise à jour…' : 'Mettre à jour le mot de passe' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
