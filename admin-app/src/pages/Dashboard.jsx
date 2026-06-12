import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../api'
import {
  BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer,
  LineChart, Line, CartesianGrid
} from 'recharts'

const STATUT_COLORS = {
  recu: 'bg-gray-100 text-gray-700',
  en_examen: 'bg-blue-100 text-blue-700',
  attribue: 'bg-orange-100 text-orange-700',
  en_instruction: 'bg-purple-100 text-purple-700',
  traite: 'bg-green-100 text-green-700',
  classe: 'bg-red-100 text-red-700',
}
const STATUT_LABELS = {
  recu: 'Reçu', en_examen: 'En examen', attribue: 'Attribué',
  en_instruction: 'En instruction', traite: 'Traité', classe: 'Classé'
}

export default function Dashboard() {
  const [stats, setStats] = useState(null)
  const [signalements, setSignalements] = useState([])

  useEffect(() => {
    api.get('/admin/stats').then(r => setStats(r.data)).catch(() => {})
    api.get('/admin/signalements').then(r => setSignalements(r.data.data || [])).catch(() => {})
  }, [])

  return (
    <div className="p-6 max-w-7xl">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Tableau de bord</h1>

      {stats && (
        <>
          {/* KPI Cards */}
          <div className="grid grid-cols-4 gap-4 mb-6">
            <div className="bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-500">
              <div className="text-3xl font-bold text-gray-900">{stats.total}</div>
              <div className="text-sm text-gray-500 mt-1">Total signalements</div>
            </div>
            <div className="bg-white rounded-xl p-5 shadow-sm border-l-4 border-orange-400">
              <div className="text-3xl font-bold text-orange-500">{stats.non_attribues || 0}</div>
              <div className="text-sm text-gray-500 mt-1">Non attribués</div>
            </div>
            <div className="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
              <div className="text-3xl font-bold text-green-600">{stats.taux_traitement}%</div>
              <div className="text-sm text-gray-500 mt-1">Taux de traitement</div>
            </div>
            <div className="bg-white rounded-xl p-5 shadow-sm border-l-4 border-yellow-400">
              <div className="text-3xl font-bold text-yellow-600">{stats.delai_moyen}j</div>
              <div className="text-sm text-gray-500 mt-1">Délai moyen</div>
            </div>
          </div>

          {/* Charts */}
          <div className="grid grid-cols-2 gap-6 mb-6">
            <div className="bg-white rounded-xl p-5 shadow-sm">
              <h2 className="font-bold text-gray-800 mb-4">Signalements par catégorie</h2>
              <ResponsiveContainer width="100%" height={220}>
                <BarChart data={stats.par_categorie?.map(c => ({
                  name: c.categorie.split(' ')[0],
                  total: c.total,
                  fullName: c.categorie
                }))}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                  <XAxis dataKey="name" tick={{ fontSize: 11 }} />
                  <YAxis tick={{ fontSize: 11 }} />
                  <Tooltip formatter={(val, name, props) => [val, props.payload.fullName]} />
                  <Bar dataKey="total" fill="#009A44" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
            <div className="bg-white rounded-xl p-5 shadow-sm">
              <h2 className="font-bold text-gray-800 mb-4">Évolution sur 6 mois</h2>
              <ResponsiveContainer width="100%" height={220}>
                <LineChart data={stats.evolution_6_mois}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                  <XAxis dataKey="mois" tick={{ fontSize: 11 }} />
                  <YAxis tick={{ fontSize: 11 }} />
                  <Tooltip />
                  <Line type="monotone" dataKey="total" stroke="#003F87" strokeWidth={2} dot={{ fill: '#003F87' }} />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </div>
        </>
      )}

      {/* Derniers signalements */}
      <div className="bg-white rounded-xl shadow-sm overflow-hidden">
        <div className="flex justify-between items-center px-5 py-4 border-b">
          <h2 className="font-bold text-gray-800">Derniers signalements</h2>
          <Link to="/signalements" title="Afficher la liste complète des signalements" className="text-blue-600 text-sm hover:underline font-medium">
            Voir tout →
          </Link>
        </div>
        <table className="w-full text-sm">
          <thead className="bg-gray-50">
            <tr className="text-left text-gray-500 text-xs uppercase tracking-wide">
              <th className="px-5 py-3">Code</th>
              <th className="px-5 py-3">Catégorie</th>
              <th className="px-5 py-3">Province</th>
              <th className="px-5 py-3">Statut</th>
              <th className="px-5 py-3">Date</th>
            </tr>
          </thead>
          <tbody>
            {signalements.slice(0, 5).map(s => (
              <tr key={s.id} className="border-t hover:bg-gray-50">
                <td className="px-5 py-3">
                  <Link to={`/signalements/${s.id}`} className="font-mono text-xs text-blue-600 hover:underline">
                    {s.code}
                  </Link>
                </td>
                <td className="px-5 py-3 text-xs">{s.categorie}</td>
                <td className="px-5 py-3 text-xs">{s.province}</td>
                <td className="px-5 py-3">
                  <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${STATUT_COLORS[s.statut]}`}>
                    {STATUT_LABELS[s.statut]}
                  </span>
                </td>
                <td className="px-5 py-3 text-xs text-gray-500">
                  {new Date(s.created_at).toLocaleDateString('fr-FR')}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
