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
  }
}
