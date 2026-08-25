export default defineNuxtRouteMiddleware((to) => {
  if (import.meta.server) return

  const authStore = useAuthStore()
  authStore.hydrater()

  const estPublique = to.path === '/connexion'

  if (!authStore.estConnecte && !estPublique) {
    return navigateTo('/connexion')
  }

  if (authStore.estConnecte && estPublique) {
    return navigateTo(authStore.accueilParRole())
  }

  // Contrôle d'accès par section
  if (to.path.startsWith('/admin') && !authStore.estAdmin) {
    return navigateTo(authStore.accueilParRole())
  }
  if (to.path.startsWith('/encadrant') && !['admin', 'encadrant'].includes(authStore.role)) {
    return navigateTo(authStore.accueilParRole())
  }
  if (to.path.startsWith('/stagiaire') && !authStore.estStagiaire) {
    return navigateTo(authStore.accueilParRole())
  }
})
