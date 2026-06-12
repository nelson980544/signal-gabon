import { Outlet, NavLink, useNavigate } from 'react-router-dom'
import { useAuth } from '../AuthContext'
import api from '../api'
import SessionWarning from './SessionWarning'

export default function Layout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    try { await api.post('/auth/logout') } catch {}
    logout()
    navigate('/login')
  }

  const navClass = ({ isActive }) =>
    `flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors ${
      isActive
        ? 'bg-white/20 text-white'
        : 'text-white/70 hover:bg-white/10 hover:text-white'
    }`

  return (
    <div className="flex h-screen bg-gray-100 min-w-screen overflow-hidden">
      {/* Sidebar */}
      <aside className="w-60 bg-blue-900 flex flex-col flex-shrink-0 overflow-y-auto">
        <div className="p-5 border-b border-white/10">
          <div className="text-white font-bold text-lg">🛡️ SignalGabon</div>
          <div className="text-blue-300 text-xs mt-0.5">Interface administration</div>
        </div>

        <div className="p-4 border-b border-white/10">
          <div className="text-white font-semibold text-sm truncate">{user?.name}</div>
          <div className="text-blue-300 text-xs capitalize mt-0.5">
            {user?.role === 'superviseur' ? '⭐ Superviseur' : '👤 Agent'}
          </div>
        </div>

        <nav className="flex-1 p-3 space-y-1">
          <NavLink to="/dashboard" title="Vue d'ensemble : indicateurs clés et derniers signalements" className={navClass}>📊 Tableau de bord</NavLink>
          <NavLink to="/signalements" title="Consulter, filtrer et traiter tous les signalements" className={navClass}>📋 Signalements</NavLink>
          <NavLink to="/stats" title="Statistiques détaillées de la plateforme" className={navClass}>📈 Statistiques</NavLink>
          {user?.role === 'superviseur' && (
            <NavLink to="/agents" title="Liste des agents et leur charge de travail" className={navClass}>👥 Agents</NavLink>
          )}
          {user?.role === 'superviseur' && (
            <NavLink to="/guide" title="Guide d'utilisation pour les superviseurs" className={navClass}>📖 Guide</NavLink>
          )}
          {user?.role === 'agent' && (
            <NavLink to="/guide-agent" title="Guide d'utilisation pour les agents" className={navClass}>📖 Guide</NavLink>
          )}
        </nav>

        <div className="p-3 border-t border-white/10">
          <button
            onClick={handleLogout}
            title="Fermer la session et revenir à la page de connexion"
            className="w-full text-white/70 hover:text-white text-sm py-2 px-4 rounded-lg hover:bg-white/10 transition text-left flex items-center gap-2"
          >
            🚪 Déconnexion
          </button>
        </div>
      </aside>

      {/* Contenu principal */}
      <main className="flex-1 overflow-auto">
        <Outlet />
      </main>

      <SessionWarning />
    </div>
  )
}
