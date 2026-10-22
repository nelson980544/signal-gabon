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
      // BASE_URL vaut '/' en local et '/admin/' quand l'app est servie
      // sous un sous-chemin du déploiement unifié.
      const base = import.meta.env.BASE_URL.replace(/\/$/, '')
      // Sauvegarder l'URL courante (sans le base path) pour y revenir
      // après reconnexion — le routeur l'interprète relativement au basename.
      const current =
        window.location.pathname.slice(base.length) + window.location.search
      if (current !== '/login') {
        sessionStorage.setItem('sg_redirect_after_login', current)
      }
      window.location.href = `${base}/login`
    }
    return Promise.reject(err)
  }
)

// ── Preuves ────────────────────────────────────────────────────────────────
/**
 * Récupère une preuve et renvoie une URL d'objet exploitable par <img>,
 * <iframe>, <audio> ou un lien de téléchargement.
 *
 * Un <a href> ne peut pas porter le jeton Sanctum : le fichier passe donc par
 * l'instance axios, qui ajoute l'en-tête et gère déjà l'expiration de session.
 *
 * L'appelant DOIT libérer l'URL avec URL.revokeObjectURL quand il a fini,
 * sinon le fichier reste en mémoire jusqu'au rechargement de la page.
 */
export async function recupererPreuve(signalementId, preuveId) {
  const r = await api.get(`/admin/signalements/${signalementId}/preuves/${preuveId}`, {
    responseType: 'blob'
  })
  return { url: URL.createObjectURL(r.data), type: r.data.type }
}

export default api
