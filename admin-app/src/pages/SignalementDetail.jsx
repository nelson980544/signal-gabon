import { useEffect, useState } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useAuth } from '../AuthContext'
import api from '../api'

const STATUTS = ['recu', 'en_examen', 'attribue', 'en_instruction', 'traite', 'classe']
const STATUT_LABELS = {
  recu: 'Reçu',
  en_examen: 'En cours d\'examen',
  attribue: 'Attribué à un agent',
  en_instruction: 'En instruction',
  traite: 'Traité',
  classe: 'Classé sans suite',
}
const STATUT_COLORS = {
  recu: 'bg-gray-100 text-gray-700',
  en_examen: 'bg-blue-100 text-blue-700',
  attribue: 'bg-orange-100 text-orange-700',
  en_instruction: 'bg-purple-100 text-purple-700',
  traite: 'bg-green-100 text-green-700',
  classe: 'bg-red-100 text-red-700',
}

export default function SignalementDetail() {
  const { id } = useParams()
  const { user } = useAuth()
  const navigate = useNavigate()
  const [s, setS] = useState(null)
  const [agents, setAgents] = useState([])
  const [form, setForm] = useState({ statut: '', agent_id: '', message_agent: '' })
  const [saving, setSaving] = useState(false)
  const [saved, setSaved] = useState(false)
  const [erreur, setErreur] = useState('')

  useEffect(() => {
    api.get(`/admin/signalements/${id}`).then(r => {
      setS(r.data)
      setForm({
        statut: r.data.statut,
        agent_id: r.data.agent_id || '',
        message_agent: r.data.message_agent || ''
      })
    }).catch(() => navigate('/signalements'))

    if (user?.role === 'superviseur') {
      api.get('/admin/agents').then(r => setAgents(r.data)).catch(() => {})
    }
  }, [id])

  const save = async () => {
    setSaving(true); setErreur('')
    try {
      const payload = { statut: form.statut, message_agent: form.message_agent }
      if (user?.role === 'superviseur') payload.agent_id = form.agent_id || null
      const r = await api.patch(`/admin/signalements/${id}`, payload)
      setS(prev => ({ ...prev, ...r.data }))
      setSaved(true)
      setTimeout(() => setSaved(false), 2000)
    } catch (e) {
      setErreur(e.response?.data?.message || 'Erreur lors de la sauvegarde')
    } finally { setSaving(false) }
  }

  if (!s) return (
    <div className="p-6 flex items-center justify-center h-64">
      <div className="text-gray-500">Chargement du dossier...</div>
    </div>
  )

  return (
    <div className="p-6 max-w-6xl">
      <div className="flex items-center gap-3 mb-6">
        <button
          onClick={() => navigate('/signalements')}
          className="text-blue-600 hover:underline text-sm font-medium"
        >
          ← Retour aux signalements
        </button>
        <span className="text-gray-300">|</span>
        <h1 className="text-xl font-bold text-gray-900 font-mono">{s.code}</h1>
        <span className={`px-3 py-1 rounded-full text-xs font-semibold ${STATUT_COLORS[s.statut]}`}>
          {STATUT_LABELS[s.statut]}
        </span>
      </div>

      <div className="grid grid-cols-2 gap-6">
        {/* Gauche : informations */}
        <div className="bg-white rounded-xl shadow-sm p-6 space-y-4">
          <h2 className="font-bold text-gray-800 text-base border-b pb-3">Informations du signalement</h2>

          <div className="space-y-3 text-sm">
            {[
              ['Code de suivi', s.code],
              ['Catégorie', s.categorie],
              ['Province', s.province],
              ['Ville / Village', s.ville],
              ['Date des faits', new Date(s.date_faits).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })],
              ['Date de réception', new Date(s.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })],
              ['Agent assigné', s.agent?.name || 'Non attribué'],
            ].map(([k, v]) => (
              <div key={k} className="flex justify-between items-start gap-4">
                <span className="text-gray-500 flex-shrink-0">{k}</span>
                <span className={`font-medium text-right ${!s.agent && k === 'Agent assigné' ? 'text-orange-500' : 'text-gray-900'}`}>{v}</span>
              </div>
            ))}
          </div>

          <div className="border-t pt-4">
            <div className="text-gray-500 text-sm mb-2">Description des faits</div>
            <p className="text-sm text-gray-800 bg-gray-50 rounded-lg p-4 leading-relaxed">{s.description}</p>
          </div>

          {s.preuves?.length > 0 && (
            <div className="border-t pt-4">
              <div className="text-gray-500 text-sm mb-2">Preuves jointes ({s.preuves.length})</div>
              <div className="space-y-2">
                {s.preuves.map(p => (
                  <div key={p.id} className="flex items-center gap-2 text-xs bg-blue-50 border border-blue-100 rounded-lg p-2.5">
                    <span>📄</span>
                    <span className="text-blue-700 font-medium truncate">{p.nom_fichier}</span>
                    <span className="text-gray-400 flex-shrink-0">({(p.taille / 1024).toFixed(0)} Ko)</span>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Droite : actions */}
        <div className="bg-white rounded-xl shadow-sm p-6 space-y-5">
          <h2 className="font-bold text-gray-800 text-base border-b pb-3">Actions</h2>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1.5">Statut du dossier</label>
            <select
              value={form.statut}
              onChange={e => setForm(f => ({ ...f, statut: e.target.value }))}
              className="w-full border rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none"
            >
              {STATUTS.map(s => <option key={s} value={s}>{STATUT_LABELS[s]}</option>)}
            </select>
          </div>

          {user?.role === 'superviseur' && (
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Attribuer à un agent</label>
              <select
                value={form.agent_id}
                onChange={e => setForm(f => ({ ...f, agent_id: e.target.value }))}
                className="w-full border rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none"
              >
                <option value="">— Non attribué —</option>
                {agents.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
              </select>
            </div>
          )}

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1.5">
              Message au citoyen
            </label>
            <textarea
              rows={5}
              value={form.message_agent}
              onChange={e => setForm(f => ({ ...f, message_agent: e.target.value }))}
              placeholder="Ce message sera visible par le citoyen lorsqu'il consultera le statut de son dossier..."
              className="w-full border rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"
            />
            <p className="text-xs text-gray-400 mt-1">Ce message est visible publiquement par le citoyen.</p>
          </div>

          {erreur && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-3">
              <p className="text-red-600 text-sm">{erreur}</p>
            </div>
          )}

          <button
            onClick={save}
            disabled={saving}
            className={`w-full py-3 rounded-xl font-bold text-sm transition-all disabled:opacity-60 ${
              saved ? 'bg-green-500 text-white' : 'bg-blue-900 text-white hover:bg-blue-800'
            }`}
          >
            {saving ? 'Enregistrement...' : saved ? '✓ Modifications enregistrées !' : 'Enregistrer les modifications'}
          </button>
        </div>
      </div>
    </div>
  )
}
