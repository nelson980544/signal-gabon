import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../AuthContext'
import api from '../api'

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
const PROVINCES = [
  'Estuaire', 'Haut-Ogooué', 'Moyen-Ogooué', 'Ngounié',
  'Nyanga', 'Ogooué-Ivindo', 'Ogooué-Lolo', 'Ogooué-Maritime', 'Woleu-Ntem'
]

export default function Signalements() {
  const { user } = useAuth()
  const [data, setData] = useState(null)
  const [agents, setAgents] = useState([])
  const [filtres, setFiltres] = useState({ statut: '', categorie: '', province: '', agent_id: '' })
  const [page, setPage] = useState(1)

  useEffect(() => {
    if (user?.role === 'superviseur') {
      api.get('/admin/agents').then(r => setAgents(r.data)).catch(() => {})
    }
  }, [user])

  useEffect(() => {
    const params = new URLSearchParams({ page })
    Object.entries(filtres).forEach(([k, v]) => { if (v) params.append(k, v) })
    api.get(`/admin/signalements?${params}`).then(r => setData(r.data)).catch(() => {})
  }, [filtres, page])

  const resetFiltres = () => {
    setFiltres({ statut: '', categorie: '', province: '', agent_id: '' })
    setPage(1)
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Signalements</h1>

      {/* Filtres */}
      <div className="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div className="flex gap-3 flex-wrap items-center">
          <select
            value={filtres.statut}
            onChange={e => { setFiltres(f => ({ ...f, statut: e.target.value })); setPage(1) }}
            className="border rounded-lg px-3 py-2 text-sm bg-white"
          >
            <option value="">Tous les statuts</option>
            {Object.entries(STATUT_LABELS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
          </select>

          <select
            value={filtres.province}
            onChange={e => { setFiltres(f => ({ ...f, province: e.target.value })); setPage(1) }}
            className="border rounded-lg px-3 py-2 text-sm bg-white"
          >
            <option value="">Toutes les provinces</option>
            {PROVINCES.map(p => <option key={p} value={p}>{p}</option>)}
          </select>

          {user?.role === 'superviseur' && (
            <select
              value={filtres.agent_id}
              onChange={e => { setFiltres(f => ({ ...f, agent_id: e.target.value })); setPage(1) }}
              className="border rounded-lg px-3 py-2 text-sm bg-white"
            >
              <option value="">Tous les agents</option>
              {agents.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
            </select>
          )}

          <button
            onClick={resetFiltres}
            title="Effacer tous les filtres et revenir à la première page"
            className="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 hover:bg-gray-50 rounded-lg"
          >
            ✕ Réinitialiser
          </button>

          {data && (
            <span className="text-xs text-gray-500 ml-auto">
              {data.total} résultat(s)
            </span>
          )}
        </div>
      </div>

      {/* Tableau */}
      <div className="bg-white rounded-xl shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm min-w-max">
            <thead className="bg-gray-50 border-b">
              <tr className="text-left text-gray-500 text-xs uppercase tracking-wide">
                <th className="px-4 py-3">Code</th>
                <th className="px-4 py-3">Catégorie</th>
                <th className="px-4 py-3">Province</th>
                <th className="px-4 py-3">Statut</th>
                <th className="px-4 py-3">Agent</th>
                <th className="px-4 py-3">Date</th>
                <th className="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody>
              {data?.data?.map(s => (
                <tr key={s.id} className="border-t hover:bg-gray-50">
                  <td className="px-4 py-3">
                    <span className="font-mono text-xs text-blue-600">{s.code}</span>
                  </td>
                  <td className="px-4 py-3 text-xs text-gray-700">{s.categorie}</td>
                  <td className="px-4 py-3 text-xs text-gray-600">{s.province}</td>
                  <td className="px-4 py-3">
                    <span className={`px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap ${STATUT_COLORS[s.statut]}`}>
                      {STATUT_LABELS[s.statut]}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-xs text-gray-500">
                    {s.agent?.name || <span className="text-orange-500">Non attribué</span>}
                  </td>
                  <td className="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                    {new Date(s.created_at).toLocaleDateString('fr-FR')}
                  </td>
                  <td className="px-4 py-3">
                    <Link
                      to={`/signalements/${s.id}`}
                      title={`Ouvrir le détail du dossier ${s.code}`}
                      className="text-blue-600 hover:underline text-xs font-medium whitespace-nowrap"
                    >
                      Voir →
                    </Link>
                  </td>
                </tr>
              ))}
              {data?.data?.length === 0 && (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center text-gray-400 text-sm">
                    Aucun signalement trouvé
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {data && data.last_page > 1 && (
          <div className="flex justify-between items-center px-4 py-3 border-t text-sm text-gray-600">
            <span>Page {data.current_page} sur {data.last_page}</span>
            <div className="flex gap-2">
              <button
                disabled={!data.prev_page_url}
                onClick={() => setPage(p => p - 1)}
                title="Afficher la page précédente de résultats"
                className="px-3 py-1.5 border rounded-lg disabled:opacity-40 hover:bg-gray-50 text-xs"
              >
                ← Précédent
              </button>
              <button
                disabled={!data.next_page_url}
                onClick={() => setPage(p => p + 1)}
                title="Afficher la page suivante de résultats"
                className="px-3 py-1.5 border rounded-lg disabled:opacity-40 hover:bg-gray-50 text-xs"
              >
                Suivant →
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
