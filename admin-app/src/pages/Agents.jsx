import { useEffect, useState } from 'react'
import api from '../api'

export default function Agents() {
  const [agents, setAgents] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api.get('/admin/agents').then(r => setAgents(r.data)).catch(() => {}).finally(() => setLoading(false))
  }, [])

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Agents CNLCEI</h1>

      <div className="bg-white rounded-xl shadow-sm overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-gray-400">Chargement...</div>
        ) : (
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr className="text-left text-gray-500 text-xs uppercase tracking-wide">
                <th className="px-5 py-3">#</th>
                <th className="px-5 py-3">Nom</th>
                <th className="px-5 py-3">Email</th>
                <th className="px-5 py-3">Statut</th>
              </tr>
            </thead>
            <tbody>
              {agents.map((a, i) => (
                <tr key={a.id} className="border-t hover:bg-gray-50">
                  <td className="px-5 py-3 text-gray-400 text-xs">{i + 1}</td>
                  <td className="px-5 py-3">
                    <div className="font-medium text-gray-900">{a.name}</div>
                  </td>
                  <td className="px-5 py-3 text-gray-500">{a.email}</td>
                  <td className="px-5 py-3">
                    <span className="bg-green-100 text-green-700 px-2.5 py-0.5 rounded-full text-xs font-medium">
                      ● Actif
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  )
}
