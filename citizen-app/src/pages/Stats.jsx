import { useEffect, useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import api from '../api'

export default function Stats() {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)
  const navigate = useNavigate()

  useEffect(() => {
    api.get('/stats/publiques').then(r => setStats(r.data)).catch(() => {}).finally(() => setLoading(false))
  }, [])

  if (loading) return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="text-gray-500 text-lg">Chargement des statistiques...</div>
    </div>
  )

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="hidden md:flex bg-bleu text-white px-8 py-4 items-center justify-between">
        <Link to="/" className="text-2xl font-bold text-jaune">🛡️ SignalGabon</Link>
        <div className="flex gap-6 text-sm">
          <Link to="/" className="hover:text-jaune">Accueil</Link>
          <Link to="/signaler" className="hover:text-jaune">Signaler</Link>
          <Link to="/suivi" className="hover:text-jaune">Suivre</Link>
        </div>
      </nav>

      <div className="max-w-xl md:max-w-4xl mx-auto px-4 py-8">
        <div className="flex items-center gap-2 mb-6 md:hidden">
          <button onClick={() => navigate('/')} className="text-bleu font-medium">← Retour</button>
          <h1 className="text-xl font-bold text-bleu flex-1 text-center">Statistiques publiques</h1>
          <div className="w-16" />
        </div>
        <h1 className="hidden md:block text-3xl font-bold text-bleu text-center mb-8">Statistiques publiques</h1>

        {stats && (
          <>
            {/* KPI */}
            <div className="grid grid-cols-3 gap-4 mb-8">
              <div className="bg-white rounded-xl p-4 text-center shadow-sm border-t-4 border-vert">
                <div className="text-3xl font-bold text-vert">{stats.total}</div>
                <div className="text-xs text-gray-500 mt-1">Signalements reçus</div>
              </div>
              <div className="bg-white rounded-xl p-4 text-center shadow-sm border-t-4 border-bleu">
                <div className="text-3xl font-bold text-bleu">{stats.taux_traitement}%</div>
                <div className="text-xs text-gray-500 mt-1">Taux traitement</div>
                <div className="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                  <div className="bg-bleu h-1.5 rounded-full" style={{ width: `${stats.taux_traitement}%` }} />
                </div>
              </div>
              <div className="bg-white rounded-xl p-4 text-center shadow-sm border-t-4 border-jaune">
                <div className="text-3xl font-bold text-yellow-500">{stats.delai_moyen}j</div>
                <div className="text-xs text-gray-500 mt-1">Délai moyen</div>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Par catégorie */}
              <div className="bg-white rounded-xl shadow-sm p-5">
                <h2 className="font-bold text-gray-800 mb-4">Par catégorie</h2>
                <div className="space-y-3">
                  {stats.par_categorie?.map(c => {
                    const pct = stats.total > 0 ? Math.round(c.total / stats.total * 100) : 0
                    return (
                      <div key={c.categorie}>
                        <div className="flex justify-between text-xs mb-1">
                          <span className="text-gray-600 truncate pr-2">{c.categorie}</span>
                          <span className="font-bold text-gray-900 flex-shrink-0">{c.total}</span>
                        </div>
                        <div className="w-full bg-gray-100 rounded-full h-2">
                          <div className="bg-vert h-2 rounded-full transition-all" style={{ width: `${pct}%` }} />
                        </div>
                      </div>
                    )
                  })}
                </div>
              </div>

              {/* Par province */}
              <div className="bg-white rounded-xl shadow-sm p-5">
                <h2 className="font-bold text-gray-800 mb-4">Par province</h2>
                <div className="space-y-3">
                  {stats.par_province?.map(p => {
                    const pct = stats.total > 0 ? Math.round(p.total / stats.total * 100) : 0
                    return (
                      <div key={p.province}>
                        <div className="flex justify-between text-xs mb-1">
                          <span className="text-gray-600 truncate pr-2">{p.province}</span>
                          <span className="font-bold text-gray-900 flex-shrink-0">{p.total}</span>
                        </div>
                        <div className="w-full bg-gray-100 rounded-full h-2">
                          <div className="bg-bleu h-2 rounded-full transition-all" style={{ width: `${pct}%` }} />
                        </div>
                      </div>
                    )
                  })}
                </div>
              </div>

              {/* Évolution 6 mois */}
              <div className="bg-white rounded-xl shadow-sm p-5 md:col-span-2">
                <h2 className="font-bold text-gray-800 mb-4">Évolution sur 6 mois</h2>
                <div className="flex items-end gap-3 h-36">
                  {stats.evolution_6_mois?.map((m, i) => {
                    const max = Math.max(...stats.evolution_6_mois.map(x => x.total), 1)
                    const h = Math.max(m.total / max * 100, 4)
                    return (
                      <div key={i} className="flex-1 flex flex-col items-center gap-1">
                        <div className="text-xs font-bold text-gray-700">{m.total}</div>
                        <div className="w-full bg-bleu rounded-t-sm transition-all" style={{ height: `${h}%` }} />
                        <div className="text-xs text-gray-400 text-center whitespace-nowrap">{m.mois.split(' ')[0]}</div>
                      </div>
                    )
                  })}
                </div>
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  )
}
