import { createContext, useContext, useState, useEffect, useRef, useCallback } from 'react'
import { setToken, clearToken, saveSession, loadSession, clearSession } from './api'
import api from './api'

const AuthContext = createContext(null)
export const useAuth = () => useContext(AuthContext)

// Durée de session : 6 heures en ms
const SESSION_DURATION_MS = 6 * 60 * 60 * 1000
// Rafraîchissement auto toutes les 30 minutes
const REFRESH_INTERVAL_MS = 30 * 60 * 1000
// Alerte 5 minutes avant expiration
const WARN_BEFORE_MS = 5 * 60 * 1000

export function AuthProvider({ children }) {
  const [user, setUser]           = useState(null)
  const [expiresAt, setExpiresAt] = useState(null)
  const [showWarning, setShowWarning] = useState(false)

  const refreshTimerRef = useRef(null)
  const warnTimerRef    = useRef(null)

  // ── Programmer les timers ────────────────────────────────────────────────
  const scheduleTimers = useCallback((expiry) => {
    clearTimeout(refreshTimerRef.current)
    clearTimeout(warnTimerRef.current)

    const now        = Date.now()
    const expiryMs   = new Date(expiry).getTime()
    const msLeft     = expiryMs - now

    // Timer d'alerte (5 min avant)
    const warnIn = msLeft - WARN_BEFORE_MS
    if (warnIn > 0) {
      warnTimerRef.current = setTimeout(() => setShowWarning(true), warnIn)
    } else if (msLeft > 0) {
      // Moins de 5 min restantes : afficher l'alerte tout de suite
      setShowWarning(true)
    }

    // Auto-refresh à mi-chemin (toutes les 30 min)
    const refreshIn = Math.min(REFRESH_INTERVAL_MS, Math.max(0, msLeft - WARN_BEFORE_MS - 60_000))
    if (refreshIn > 0) {
      refreshTimerRef.current = setTimeout(() => {
        refreshSession()
      }, refreshIn)
    }
  }, []) // eslint-disable-line react-hooks/exhaustive-deps

  // ── Restaurer la session au montage ─────────────────────────────────────
  useEffect(() => {
    const session = loadSession()
    if (session) {
      setToken(session.token)
      setUser(session.user)
      setExpiresAt(session.expiresAt)
      scheduleTimers(session.expiresAt)
    }
    return () => {
      clearTimeout(refreshTimerRef.current)
      clearTimeout(warnTimerRef.current)
    }
  }, []) // eslint-disable-line react-hooks/exhaustive-deps

  // ── Login ────────────────────────────────────────────────────────────────
  const login = useCallback((userData, token) => {
    const expiry = new Date(Date.now() + SESSION_DURATION_MS).toISOString()
    setToken(token)
    setUser(userData)
    setExpiresAt(expiry)
    setShowWarning(false)
    saveSession(token, userData, expiry)
    scheduleTimers(expiry)
  }, [scheduleTimers])

  // ── Logout ───────────────────────────────────────────────────────────────
  const logout = useCallback(() => {
    clearTimeout(refreshTimerRef.current)
    clearTimeout(warnTimerRef.current)
    clearToken()
    clearSession()
    setUser(null)
    setExpiresAt(null)
    setShowWarning(false)
  }, [])

  // ── Refresh token ────────────────────────────────────────────────────────
  const refreshSession = useCallback(async () => {
    try {
      const { data } = await api.post('/auth/refresh')
      const expiry = data.expires_at || new Date(Date.now() + SESSION_DURATION_MS).toISOString()
      setToken(data.token)
      setExpiresAt(expiry)
      setShowWarning(false)
      // Mettre à jour le localStorage avec le nouveau token
      const session = loadSession()
      if (session) saveSession(data.token, session.user, expiry)
      scheduleTimers(expiry)
    } catch {
      // Si le refresh échoue, la session est vraiment expirée
      logout()
    }
  }, [logout, scheduleTimers])

  return (
    <AuthContext.Provider value={{
      user,
      expiresAt,
      showWarning,
      login,
      logout,
      refreshSession,
    }}>
      {children}
    </AuthContext.Provider>
  )
}
