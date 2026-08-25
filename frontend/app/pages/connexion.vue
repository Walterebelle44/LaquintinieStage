<script setup>
definePageMeta({ layout: 'default' })

const email = ref('')
const password = ref('')
const chargement = ref(false)
const erreur = ref('')

const authStore = useAuthStore()
const api = useApi()

const seConnecter = async () => {
  erreur.value = ''
  chargement.value = true
  try {
    const res = await api.post('/login', { email: email.value, password: password.value })
    authStore.definir(res.token, res.user)
    navigateTo(authStore.accueilParRole())
  } catch (e) {
    erreur.value = e?.data?.message
      || e?.data?.errors?.email?.[0]
      || 'Une erreur est survenue. Vérifiez vos identifiants.'
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-brand-50 via-white to-slate-50 p-4">
    <div class="w-full max-w-md">
      <div class="flex flex-col items-center mb-8">
        <div class="w-14 h-14 rounded-2xl bg-brand-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-brand-600/20">
          HL
        </div>
        <h1 class="mt-4 text-xl font-semibold text-slate-800 text-center">
          Gestion des Stagiaires
        </h1>
        <p class="text-sm text-slate-500 text-center mt-1">
          Hôpital Laquintinie de Douala — Service Informatique
        </p>
      </div>

      <form class="card p-7" @submit.prevent="seConnecter">
        <div v-if="erreur" class="mb-4 px-3 py-2.5 rounded-lg bg-red-50 text-red-600 text-sm">
          {{ erreur }}
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Adresse e-mail</label>
            <input v-model="email" type="email" required autocomplete="username" class="input-field" placeholder="vous@laquintinie.cm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe</label>
            <input v-model="password" type="password" required autocomplete="current-password" class="input-field" placeholder="••••••••" />
          </div>
        </div>

        <button type="submit" :disabled="chargement" class="btn-primary w-full mt-6">
          {{ chargement ? 'Connexion en cours…' : 'Se connecter' }}
        </button>
      </form>

      <p class="text-center text-xs text-slate-400 mt-6">
        Accès réservé au personnel autorisé du service informatique.
      </p>
    </div>
  </div>
</template>
