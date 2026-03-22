import axios from 'axios'

const STORAGE_KEY = 'sg_session'

// ── Persistance ────────────────────────────────────────────────────────────
export function saveSession(token, user, expiresAt) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify({ token, user, expiresAt }))
}

export function loadSession() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return null
    const session = JSON.parse(raw)
    // Session expirée côté client → on la supprime
    if (session.expiresAt && Date.now() > new Date(session.expiresAt).getTime()) {
      clearSession()
      return null
    }
    return session
  } catch {
    return null
  }
}

export function clearSession() {
  localStorage.removeItem(STORAGE_KEY)
}

// ── Token en mémoire (performance) ────────────────────────────────────────
let token = null
export const setToken = (t) => { token = t }
export const getToken = () => token
export const clearToken = () => { token = null }

// ── Instance axios ─────────────────────────────────────────────────────────
const api = axios.create({
  baseURL: (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').trim()
})

api.interceptors.request.use(config => {
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  r => r,
  err => {
    if (err.response?.status === 401) {
      clearToken()
      clearSession()
      // Sauvegarder l'URL courante pour y revenir après reconnexion
      const current = window.location.pathname + window.location.search
      if (current !== '/login') {
        sessionStorage.setItem('sg_redirect_after_login', current)
      }
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

export default api
