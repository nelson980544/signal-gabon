import { useEffect, useState } from 'react'
import { useAuth } from '../AuthContext'
import api from '../api'
import {
  BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer,
  LineChart, Line, CartesianGrid
} from 'recharts'

export default function StatsAdmin() {
  const { user } = useAuth()
  const [stats, setStats] = useState(null)

  useEffect(() => {
    api.get('/admin/stats').then(r => setStats(r.data)).catch(() => {})
  }, [])

  if (!stats) return (
    <div className="p-6 flex items-center justify-center h-64">
      <div className="text-gray-500">Chargement des statistiques...</div>
    </div>
  )

  return (
    <div className="p-6 max-w-6xl">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Statistiques</h1>

      {/* KPIs */}
      <div className="grid grid-cols-4 gap-4 mb-6">
        <div className="bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-500">
          <div className="text-3xl font-bold text-gray-900">{stats.total}</div>
          <div className="text-sm text-gray-500 mt-1">Total</div>
        </div>
        <div className="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
          <div className="text-3xl font-bold text-green-600">{stats.taux_traitement}%</div>
          <div className="text-sm text-gray-500 mt-1">Taux de traitement</div>
        </div>
        <div className="bg-white rounded-xl p-5 shadow-sm border-l-4 border-yellow-400">
          <div className="text-3xl font-bold text-yellow-600">{stats.delai_moyen}j</div>
          <div className="text-sm text-gray-500 mt-1">Délai moyen</div>
        </div>
        <div className="bg-white rounded-xl p-5 shadow-sm border-l-4 border-orange-400">
          <div className="text-3xl font-bold text-orange-500">{stats.non_attribues}</div>
          <div className="text-sm text-gray-500 mt-1">Non attribués</div>
        </div>
      </div>

      {/* Graphiques */}
      <div className="grid grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl p-5 shadow-sm">
          <h2 className="font-bold text-gray-800 mb-4">Par catégorie</h2>
          <ResponsiveContainer width="100%" height={220}>
            <BarChart data={stats.par_categorie?.map(c => ({
              name: c.categorie.split(' ')[0],
              total: c.total,
              fullName: c.categorie
            }))}>
              <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
              <XAxis dataKey="name" tick={{ fontSize: 10 }} />
              <YAxis tick={{ fontSize: 10 }} />
              <Tooltip formatter={(val, name, props) => [val, props.payload.fullName]} />
              <Bar dataKey="total" fill="#009A44" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white rounded-xl p-5 shadow-sm">
          <h2 className="font-bold text-gray-800 mb-4">Par province</h2>
          <ResponsiveContainer width="100%" height={220}>
            <BarChart data={stats.par_province?.map(p => ({
              name: p.province.replace('Ogooué-', 'Og.-'),
              total: p.total,
              fullName: p.province
            }))}>
              <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
              <XAxis dataKey="name" tick={{ fontSize: 10 }} />
              <YAxis tick={{ fontSize: 10 }} />
              <Tooltip formatter={(val, name, props) => [val, props.payload.fullName]} />
              <Bar dataKey="total" fill="#003F87" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white rounded-xl p-5 shadow-sm">
          <h2 className="font-bold text-gray-800 mb-4">Évolution sur 6 mois</h2>
          <ResponsiveContainer width="100%" height={220}>
            <LineChart data={stats.evolution_6_mois}>
              <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
              <XAxis dataKey="mois" tick={{ fontSize: 10 }} />
              <YAxis tick={{ fontSize: 10 }} />
              <Tooltip />
              <Line type="monotone" dataKey="total" stroke="#003F87" strokeWidth={2} dot={{ fill: '#003F87', r: 4 }} />
            </LineChart>
          </ResponsiveContainer>
        </div>

        {user?.role === 'superviseur' && stats.par_agent && (
          <div className="bg-white rounded-xl p-5 shadow-sm">
            <h2 className="font-bold text-gray-800 mb-4">Signalements par agent</h2>
            <div className="space-y-3">
              {stats.par_agent.map((a, i) => {
                const max = Math.max(...stats.par_agent.map(x => x.total), 1)
                const pct = Math.round(a.total / max * 100)
                return (
                  <div key={i}>
                    <div className="flex justify-between text-sm mb-1">
                      <span className="text-gray-700 text-xs truncate pr-2">{a.agent}</span>
                      <span className="font-bold text-gray-900 flex-shrink-0">{a.total}</span>
                    </div>
                    <div className="w-full bg-gray-100 rounded-full h-2">
                      <div className="bg-blue-900 h-2 rounded-full" style={{ width: `${pct}%` }} />
                    </div>
                  </div>
                )
              })}
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
