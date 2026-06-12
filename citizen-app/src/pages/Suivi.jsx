import { useState, useEffect } from 'react'
import { useNavigate, useLocation, Link } from 'react-router-dom'
import api from '../api'
import Tooltip from '../components/Tooltip'

const STORAGE_KEY = 'signalGabon_codes'

const STATUTS = [
  { id: 'recu', label: 'Reçu', icon: '🟢' },
  { id: 'en_examen', label: 'En cours d\'examen', icon: '🔵' },
  { id: 'attribue', label: 'Attribué à un agent', icon: '👤' },
  { id: 'en_instruction', label: 'En instruction', icon: '⚖️' },
  { id: 'traite', label: 'Traité', icon: '✅' },
  { id: 'classe', label: 'Classé sans suite', icon: '❌' },
]

export default function Suivi() {
  const [code, setCode] = useState('')
  const [result, setResult] = useState(null)
  const [loading, setLoading] = useState(false)
  const [erreur, setErreur] = useState('')
  const [codesLocaux, setCodesLocaux] = useState([])
  const navigate = useNavigate()
  const location = useLocation()

  // Pré-remplir depuis la navigation (link depuis Confirmation) ou localStorage
  useEffect(() => {
    const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
    setCodesLocaux(saved)
    // Priorité : code passé via state (lien depuis Confirmation) > dernier code localStorage
    const prefill = location.state?.code || saved[0] || ''
    if (prefill) setCode(prefill)
  }, [location.state?.code])

  const rechercher = async () => {
    if (!code.trim()) { setErreur('Veuillez saisir votre code de suivi'); return }
    setLoading(true); setErreur(''); setResult(null)
    try {
      const r = await api.get(`/signalements/${code.toUpperCase().trim()}/statut`)
      setResult(r.data)
    } catch {
      setErreur('Code introuvable. Vérifiez votre code de suivi (format : SG-XXXX-XXXX).')
    } finally { setLoading(false) }
  }

  const statutActifIndex = result ? STATUTS.findIndex(s => s.id === result.statut) : -1

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="hidden md:flex bg-bleu text-white px-8 py-4 items-center justify-between">
        <Link to="/" title="Revenir à la page d'accueil" className="text-2xl font-bold text-jaune">🛡️ SignalGabon</Link>
        <div className="flex gap-6 text-sm">
          <Link to="/" title="Revenir à la page d'accueil" className="hover:text-jaune">Accueil</Link>
          <Link to="/signaler" title="Déclarer anonymement un fait de corruption" className="hover:text-jaune">Faire un signalement</Link>
        </div>
      </nav>

      <div className="max-w-xl md:max-w-3xl mx-auto px-4 py-8">
        <div className="flex items-center gap-2 mb-6 md:hidden">
          <Tooltip texte="Revenir à la page d'accueil">
            <button onClick={() => navigate('/')} aria-label="Revenir à la page d'accueil" className="text-bleu font-medium">← Retour</button>
          </Tooltip>
          <h1 className="text-xl font-bold text-bleu flex-1 text-center">Suivre mon dossier</h1>
          <div className="w-16" />
        </div>
        <h1 className="hidden md:block text-3xl font-bold text-bleu text-center mb-8">Suivre mon dossier</h1>

        <div className="bg-white rounded-2xl shadow-sm p-6 mb-6">
          <label className="block text-sm font-medium text-gray-700 mb-2">Code de suivi</label>
          <div className="flex gap-2">
            <input
              value={code}
              onChange={e => setCode(e.target.value)}
              onKeyDown={e => e.key === 'Enter' && rechercher()}
              placeholder="SG-XXXX-XXXX"
              className="flex-1 border rounded-xl px-4 py-3 font-mono text-sm focus:ring-2 focus:ring-bleu outline-none uppercase tracking-widest"
            />
            <Tooltip texte="Rechercher mon dossier avec ce code">
              <button
                onClick={rechercher}
                disabled={loading}
                aria-label="Rechercher mon dossier avec ce code"
                className="bg-bleu text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-800 disabled:opacity-60 transition"
              >
                {loading ? '⏳' : '🔍'}
              </button>
            </Tooltip>
          </div>
          {erreur && <p className="text-red-500 text-sm mt-3 bg-red-50 rounded-lg p-2">{erreur}</p>}
          {codesLocaux.length > 0 && !result && (
            <div className="mt-3 pt-3 border-t border-gray-100">
              <p className="text-xs text-gray-500 mb-2">💾 Codes sauvegardés sur cet appareil :</p>
              <div className="flex flex-wrap gap-2">
                {codesLocaux.map(c => (
                  <button
                    key={c}
                    title={`Remplir le champ avec le code ${c}`}
                    onClick={() => { setCode(c); setResult(null); setErreur('') }}
                    className={`font-mono text-xs px-3 py-1.5 rounded-lg border transition ${c === code ? 'bg-bleu text-white border-bleu' : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-bleu hover:text-bleu'}`}
                  >
                    {c}
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>

        {result && (
          <div className="bg-white rounded-2xl shadow-sm p-6">
            <div className="flex justify-between items-start mb-6 pb-4 border-b">
              <div>
                <div className="text-xs text-gray-500 mb-1">Code du dossier</div>
                <div className="font-mono font-bold text-bleu text-xl">{result.code}</div>
              </div>
              <div className="text-right">
                <div className="text-xs text-gray-500 mb-1">Catégorie</div>
                <div className="text-sm font-medium text-gray-800">{result.categorie}</div>
                <div className="text-xs text-gray-500">{result.province}</div>
              </div>
            </div>

            {/* Timeline mobile — verticale */}
            <div className="md:hidden space-y-0 mb-6">
              {STATUTS.map((s, i) => {
                const actif = i === statutActifIndex
                const passe = i < statutActifIndex
                return (
                  <div key={s.id} className="flex gap-3">
                    <div className="flex flex-col items-center">
                      <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 ${actif ? 'bg-vert text-white ring-4 ring-vert/20' : passe ? 'bg-vert/20 text-vert' : 'bg-gray-100 text-gray-400'}`}>
                        {actif ? '●' : passe ? '✓' : s.icon}
                      </div>
                      {i < STATUTS.length - 1 && (
                        <div className={`w-0.5 h-6 ${i < statutActifIndex ? 'bg-vert' : 'bg-gray-200'}`} />
                      )}
                    </div>
                    <div className={`pt-1 pb-4 text-sm ${actif ? 'font-bold text-gray-900' : passe ? 'text-vert' : 'text-gray-400'}`}>
                      {s.label} {actif && <span className="text-xs bg-vert text-white px-2 py-0.5 rounded-full ml-1">actuel</span>}
                    </div>
                  </div>
                )
              })}
            </div>

            {/* Timeline desktop — horizontale */}
            <div className="hidden md:flex items-start mb-6 overflow-x-auto pb-2">
              {STATUTS.map((s, i) => (
                <div key={s.id} className="flex items-center flex-1 min-w-0">
                  <div className="flex flex-col items-center flex-shrink-0 w-20">
                    <div className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold ${i === statutActifIndex ? 'bg-vert text-white ring-4 ring-vert/20' : i < statutActifIndex ? 'bg-vert/20 text-vert' : 'bg-gray-100 text-gray-400'}`}>
                      {i < statutActifIndex ? '✓' : s.icon}
                    </div>
                    <div className={`text-xs mt-1 text-center leading-tight px-1 ${i === statutActifIndex ? 'font-bold text-gray-900' : i < statutActifIndex ? 'text-vert' : 'text-gray-400'}`}>
                      {s.label}
                    </div>
                  </div>
                  {i < STATUTS.length - 1 && (
                    <div className={`flex-1 h-0.5 mb-5 ${i < statutActifIndex ? 'bg-vert' : 'bg-gray-200'}`} />
                  )}
                </div>
              ))}
            </div>

            <div className="text-xs text-gray-400 text-center mb-4">
              Dernière mise à jour : {new Date(result.updated_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
            </div>

            {result.message_agent && (
              <div className="bg-bleu/5 border border-bleu/20 rounded-xl p-4 mt-2">
                <div className="text-xs font-semibold text-bleu mb-2 flex items-center gap-1">
                  💬 Message de la CNLCEI
                </div>
                <p className="text-sm text-gray-700">{result.message_agent}</p>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
