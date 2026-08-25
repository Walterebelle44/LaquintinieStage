export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null,
    user: null,
  }),

  getters: {
    estConnecte: (state) => !!state.token,
    role: (state) => state.user?.role ?? null,
    estAdmin: (state) => state.user?.role === 'admin',
    estEncadrant: (state) => state.user?.role === 'encadrant',
    estStagiaire: (state) => state.user?.role === 'stagiaire',
    nomComplet: (state) => state.user?.nom_complet ?? '',
  },

  actions: {
    hydrater() {
      if (import.meta.client) {
        const token = localStorage.getItem('gsl_token')
        const user = localStorage.getItem('gsl_user')
        if (token) this.token = token
        if (user) {
          try {
            this.user = JSON.parse(user)
          } catch {
            this.user = null
          }
        }
      }
    },

    definir(token, user) {
      this.token = token
      this.user = user
      if (import.meta.client) {
        localStorage.setItem('gsl_token', token)
        localStorage.setItem('gsl_user', JSON.stringify(user))
      }
    },

    mettreAJourUtilisateur(user) {
      this.user = user
      if (import.meta.client) {
        localStorage.setItem('gsl_user', JSON.stringify(user))
      }
    },

    clear() {
      this.token = null
      this.user = null
      if (import.meta.client) {
        localStorage.removeItem('gsl_token')
        localStorage.removeItem('gsl_user')
      }
    },

    accueilParRole() {
      if (this.estAdmin) return '/admin/tableau-de-bord'
      if (this.estEncadrant) return '/encadrant/stagiaires'
      if (this.estStagiaire) return '/stagiaire/feuille-de-route'
      return '/connexion'
    },
  },
})
