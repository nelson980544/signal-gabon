import { useAuth } from '../AuthContext'

export default function SessionWarning() {
  const { showWarning, refreshSession, logout } = useAuth()

  if (!showWarning) return null

  return (
    <div className="fixed bottom-4 right-4 z-50 w-80 bg-amber-50 border border-amber-300 rounded-xl shadow-lg p-4">
      <div className="flex items-start gap-3">
        <span className="text-xl">⏱️</span>
        <div className="flex-1">
          <p className="text-sm font-semibold text-amber-900">Session bientôt expirée</p>
          <p className="text-xs text-amber-700 mt-0.5">
            Votre session expire dans 5 minutes — cliquez pour continuer.
          </p>
          <div className="flex gap-2 mt-3">
            <button
              onClick={refreshSession}
              className="flex-1 bg-amber-600 text-white text-xs font-semibold py-1.5 px-3 rounded-lg hover:bg-amber-700 transition"
            >
              Prolonger ma session
            </button>
            <button
              onClick={logout}
              className="text-xs text-amber-700 hover:text-amber-900 px-2 transition"
            >
              Déconnexion
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
