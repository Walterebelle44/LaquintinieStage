// Client API centralisé — gère le token Sanctum et les erreurs
export function useApi() {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const request = async (path, options = {}) => {
    const headers = {
      Accept: 'application/json',
      ...(options.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
      ...(authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {}),
      ...options.headers,
    }

    try {
      return await $fetch(path, {
        baseURL: config.public.apiBase,
        headers,
        ...options,
      })
    } catch (error) {
      if (error?.response?.status === 401) {
        authStore.clear()
        navigateTo('/connexion')
      }
      throw error
    }
  }

  return {
    get: (path, params) => request(path, { method: 'GET', params }),
    post: (path, body) => request(path, { method: 'POST', body }),
    put: (path, body) => request(path, { method: 'PUT', body }),
    del: (path) => request(path, { method: 'DELETE' }),

    // Télécharge un fichier protégé par authentification. Un simple lien <a href>
    // ne peut pas transmettre le token Bearer (celui-ci ne vit que dans le JS),
    // donc on récupère le fichier via fetch authentifié puis on déclenche
    // l'enregistrement nous-mêmes.
    download: async (path, nomFichier) => {
      const blob = await request(path, { method: 'GET', responseType: 'blob' })
      const url = window.URL.createObjectURL(blob)
      const lien = document.createElement('a')
      lien.href = url
      lien.download = nomFichier || 'document'
      document.body.appendChild(lien)
      lien.click()
      lien.remove()
      window.URL.revokeObjectURL(url)
    },
  }
}