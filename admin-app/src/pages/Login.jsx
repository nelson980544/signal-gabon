import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../AuthContext'
import api from '../api'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [erreur, setErreur] = useState('')
  const [loading, setLoading] = useState(false)
  const { login } = useAuth()
  const navigate = useNavigate()

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true); setErreur('')
    try {
      const r = await api.post('/auth/login', { email, password })
      login(r.data.user, r.data.token)
      const redirect = sessionStorage.getItem('sg_redirect_after_login')
      if (redirect) { sessionStorage.removeItem('sg_redirect_after_login'); navigate(redirect) }
      else navigate('/dashboard')
    } catch (err) {
      setErreur(err.response?.data?.message || 'Erreur de connexion. Vérifiez vos identifiants.')
    } finally { setLoading(false) }
  }

  return (
    <div className="min-h-screen bg-gray-900 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div className="text-center mb-8">
          <div className="text-5xl mb-3">🛡️</div>
          <h1 className="text-2xl font-bold text-gray-900">SignalGabon</h1>
          <p className="text-gray-500 text-sm mt-1">Espace réservé aux agents CNLCEI</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
            <input
              type="email"
              value={email}
              onChange={e => setEmail(e.target.value)}
              required
              autoComplete="email"
              className="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
              placeholder="votre@email.cnlcei.ga"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
            <input
              type="password"
              value={password}
              onChange={e => setPassword(e.target.value)}
              required
              autoComplete="current-password"
              className="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
            />
          </div>
          {erreur && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-3">
              <p className="text-red-600 text-sm">{erreur}</p>
            </div>
          )}
          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-900 text-white py-3 rounded-xl font-bold hover:bg-blue-800 disabled:opacity-60 transition text-sm"
          >
            {loading ? 'Connexion en cours...' : 'Se connecter'}
          </button>
        </form>

        <div className="mt-8 p-4 bg-gray-50 rounded-xl">
          <p className="font-semibold text-gray-700 text-xs mb-3 uppercase tracking-wide">Comptes de démonstration</p>
          <div className="space-y-2 text-xs text-gray-600">
            <div className="flex flex-col gap-0.5">
              <span className="font-medium">Superviseur :</span>
              <span>superviseur@cnlcei.ga</span>
              <span className="text-gray-400">Mot de passe : Admin1234!</span>
            </div>
            <div className="border-t pt-2 flex flex-col gap-0.5">
              <span className="font-medium">Agent :</span>
              <span>jean.mboumba@cnlcei.ga</span>
              <span className="text-gray-400">Mot de passe : Agent1234!</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
