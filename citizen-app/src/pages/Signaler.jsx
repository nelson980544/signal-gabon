import { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import api from '../api'

const CATEGORIES = [
  { id: 'Administration publique', icon: '🏛️' },
  { id: 'Police et gendarmerie', icon: '🚔' },
  { id: 'Santé et hôpitaux', icon: '🏥' },
  { id: 'Éducation et universités', icon: '🎓' },
  { id: 'Douanes et frontières', icon: '✈️' },
  { id: 'Justice et tribunaux', icon: '⚖️' },
  { id: 'Marchés publics', icon: '🏗️' },
  { id: 'Autre', icon: '📦' },
]

const PROVINCES = [
  'Estuaire', 'Haut-Ogooué', 'Moyen-Ogooué', 'Ngounié',
  'Nyanga', 'Ogooué-Ivindo', 'Ogooué-Lolo', 'Ogooué-Maritime', 'Woleu-Ntem'
]

export default function Signaler() {
  const navigate = useNavigate()
  const [etape, setEtape] = useState(1)
  const [form, setForm] = useState({
    categorie: '', description: '', date_faits: '',
    province: '', ville: '', fichiers: [], certifie: false
  })
  const [loading, setLoading] = useState(false)
  const [erreur, setErreur] = useState('')

  const soumettre = async () => {
    setLoading(true); setErreur('')
    try {
      const r = await api.post('/signalements', {
        categorie: form.categorie, description: form.description,
        date_faits: form.date_faits, province: form.province, ville: form.ville
      })
      const code = r.data.code
      if (form.fichiers.length > 0) {
        const fd = new FormData()
        form.fichiers.forEach(f => fd.append('fichiers[]', f))
        await api.post(`/signalements/${code}/preuves`, fd)
      }
      navigate('/confirmation', { state: { code } })
    } catch (e) {
      setErreur(e.response?.data?.message || 'Une erreur est survenue')
    } finally { setLoading(false) }
  }

  const etapesSuivantes = () => {
    setErreur('')
    if (etape === 2) {
      if (form.description.length < 50 || !form.date_faits || !form.province || !form.ville) {
        setErreur('Veuillez remplir tous les champs (minimum 50 caractères pour la description)')
        return
      }
    }
    setEtape(e => e + 1)
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Desktop navbar */}
      <nav className="hidden md:flex bg-bleu text-white px-8 py-4 items-center justify-between">
        <Link to="/" className="text-2xl font-bold text-jaune">🛡️ SignalGabon</Link>
        <div className="flex gap-6 text-sm">
          <Link to="/" className="hover:text-jaune">Accueil</Link>
          <Link to="/suivi" className="hover:text-jaune">Suivre mon dossier</Link>
        </div>
      </nav>

      <div className="max-w-2xl md:max-w-3xl mx-auto px-4 py-6">
        <div className="flex items-center gap-2 mb-6">
          <button
            onClick={() => etape > 1 ? setEtape(e => e - 1) : navigate('/')}
            className="text-bleu hover:text-blue-700 font-medium"
          >
            ← Retour
          </button>
          <h1 className="text-xl font-bold text-bleu flex-1 text-center">Nouveau signalement</h1>
          <div className="w-16" />
        </div>

        {/* Barre de progression */}
        <div className="flex gap-2 mb-3">
          {[1, 2, 3, 4].map(i => (
            <div key={i} className={`h-2 flex-1 rounded-full transition-all ${i <= etape ? 'bg-vert' : 'bg-gray-200'}`} />
          ))}
        </div>
        <div className="text-center text-xs text-gray-500 mb-8">
          Étape {etape} sur 4 — {['Catégorie', 'Description', 'Preuves', 'Confirmation'][etape - 1]}
        </div>

        {/* Étape 1 — Catégorie */}
        {etape === 1 && (
          <div>
            <h2 className="text-lg font-bold text-gray-800 mb-4">Quelle est la catégorie du signalement ?</h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
              {CATEGORIES.map(c => (
                <button
                  key={c.id}
                  onClick={() => { setForm(f => ({ ...f, categorie: c.id })); setEtape(2) }}
                  className={`p-4 rounded-xl border-2 text-center transition-all ${form.categorie === c.id ? 'border-vert bg-vert/10' : 'border-gray-200 bg-white hover:border-vert hover:shadow-sm'}`}
                >
                  <div className="text-3xl mb-2">{c.icon}</div>
                  <div className="text-xs font-medium text-gray-700 leading-tight">{c.id}</div>
                </button>
              ))}
            </div>
          </div>
        )}

        {/* Étape 2 — Description */}
        {etape === 2 && (
          <div className="space-y-4">
            <h2 className="text-lg font-bold text-gray-800">Décrivez les faits</h2>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Description détaillée *</label>
              <textarea
                rows={6}
                value={form.description}
                onChange={e => setForm(f => ({ ...f, description: e.target.value }))}
                placeholder="Décrivez les faits avec précision : lieu exact, personnes impliquées, montants demandés, dates, circonstances..."
                className="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-vert focus:border-vert outline-none resize-none"
              />
              <div className={`text-xs mt-1 text-right ${form.description.length >= 50 ? 'text-vert' : 'text-red-400'}`}>
                {form.description.length} / 50 caractères minimum
              </div>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Date des faits *</label>
                <input
                  type="date"
                  value={form.date_faits}
                  onChange={e => setForm(f => ({ ...f, date_faits: e.target.value }))}
                  max={new Date().toISOString().split('T')[0]}
                  className="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-vert outline-none"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                <select
                  value={form.province}
                  onChange={e => setForm(f => ({ ...f, province: e.target.value }))}
                  className="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-vert outline-none bg-white"
                >
                  <option value="">Sélectionner une province...</option>
                  {PROVINCES.map(p => <option key={p} value={p}>{p}</option>)}
                </select>
              </div>
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-1">Ville / Village *</label>
                <input
                  type="text"
                  value={form.ville}
                  onChange={e => setForm(f => ({ ...f, ville: e.target.value }))}
                  placeholder="Ex: Libreville, Port-Gentil, Franceville..."
                  className="w-full border rounded-xl p-3 text-sm focus:ring-2 focus:ring-vert outline-none"
                />
              </div>
            </div>
            {erreur && <p className="text-red-500 text-sm bg-red-50 rounded-lg p-3">{erreur}</p>}
            <button onClick={etapesSuivantes} className="w-full bg-vert text-white py-4 rounded-xl font-bold text-lg mt-2">
              Continuer →
            </button>
          </div>
        )}

        {/* Étape 3 — Preuves */}
        {etape === 3 && (
          <div className="space-y-4">
            <h2 className="text-lg font-bold text-gray-800">Ajouter des preuves (optionnel)</h2>
            <p className="text-sm text-gray-500">Formats acceptés : images (JPG, PNG), PDF, audio (MP3, WAV) — Maximum 5 fichiers · 10 MB chacun</p>
            <label className="block border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-vert hover:bg-vert/5 transition">
              <div className="text-4xl mb-2">📁</div>
              <div className="text-sm font-medium text-gray-600">Cliquez pour sélectionner des fichiers</div>
              <div className="text-xs text-gray-400 mt-1">ou glissez-déposez ici</div>
              <input
                type="file"
                multiple
                accept=".jpg,.jpeg,.png,.pdf,.mp3,.mp4,.wav"
                className="hidden"
                onChange={e => {
                  const files = Array.from(e.target.files || [])
                  if (files.length + form.fichiers.length > 5) { setErreur('Maximum 5 fichiers autorisés'); return }
                  setErreur('')
                  setForm(f => ({ ...f, fichiers: [...f.fichiers, ...files] }))
                }}
              />
            </label>
            {form.fichiers.length > 0 && (
              <div className="space-y-2">
                {form.fichiers.map((f, i) => (
                  <div key={i} className="flex items-center justify-between bg-white border rounded-lg p-3 text-sm">
                    <div className="flex items-center gap-2 min-w-0">
                      <span className="text-lg">📄</span>
                      <span className="truncate text-gray-700">{f.name}</span>
                      <span className="text-xs text-gray-400 flex-shrink-0">({(f.size / 1024 / 1024).toFixed(1)} Mo)</span>
                    </div>
                    <button
                      onClick={() => setForm(fm => ({ ...fm, fichiers: fm.fichiers.filter((_, j) => j !== i) }))}
                      className="text-red-400 hover:text-red-600 ml-2 flex-shrink-0 font-bold"
                    >✕</button>
                  </div>
                ))}
              </div>
            )}
            {erreur && <p className="text-red-500 text-sm">{erreur}</p>}
            <div className="flex gap-3 pt-2">
              <button
                onClick={() => { setErreur(''); setEtape(4) }}
                className="flex-1 border-2 border-gray-300 text-gray-700 py-4 rounded-xl font-medium hover:bg-gray-50"
              >
                Passer cette étape →
              </button>
              <button
                onClick={() => { setErreur(''); setEtape(4) }}
                className="flex-1 bg-vert text-white py-4 rounded-xl font-bold"
              >
                Continuer →
              </button>
            </div>
          </div>
        )}

        {/* Étape 4 — Confirmation */}
        {etape === 4 && (
          <div className="space-y-4">
            <h2 className="text-lg font-bold text-gray-800">Confirmer le signalement</h2>
            <div className="bg-white rounded-xl border p-5 space-y-3 text-sm">
              <div className="flex justify-between items-center py-1 border-b border-gray-50">
                <span className="text-gray-500">Catégorie</span>
                <span className="font-medium">{form.categorie}</span>
              </div>
              <div className="flex justify-between items-center py-1 border-b border-gray-50">
                <span className="text-gray-500">Province</span>
                <span className="font-medium">{form.province}</span>
              </div>
              <div className="flex justify-between items-center py-1 border-b border-gray-50">
                <span className="text-gray-500">Ville / Village</span>
                <span className="font-medium">{form.ville}</span>
              </div>
              <div className="flex justify-between items-center py-1 border-b border-gray-50">
                <span className="text-gray-500">Date des faits</span>
                <span className="font-medium">{form.date_faits}</span>
              </div>
              {form.fichiers.length > 0 && (
                <div className="flex justify-between items-center py-1">
                  <span className="text-gray-500">Preuves jointes</span>
                  <span className="font-medium">{form.fichiers.length} fichier(s)</span>
                </div>
              )}
            </div>

            <div className="bg-vert/10 border border-vert/30 rounded-xl p-4">
              <p className="text-sm text-vert font-medium">🔒 Anonymat total garanti — Aucune donnée personnelle collectée</p>
              <p className="text-xs text-vert/70 mt-1">Votre identité ne peut pas être retracée à partir de ce signalement.</p>
            </div>

            <label className="flex items-start gap-3 cursor-pointer bg-white border rounded-xl p-4">
              <input
                type="checkbox"
                checked={form.certifie}
                onChange={e => setForm(f => ({ ...f, certifie: e.target.checked }))}
                className="mt-0.5 w-5 h-5 accent-vert flex-shrink-0"
              />
              <span className="text-sm text-gray-700">
                Je certifie que les faits rapportés sont réels à ma connaissance et que ce signalement est fait de bonne foi.
              </span>
            </label>

            {erreur && <p className="text-red-500 text-sm bg-red-50 rounded-lg p-3">{erreur}</p>}

            <button
              onClick={() => {
                if (!form.certifie) { setErreur('Veuillez cocher la case de certification pour continuer'); return }
                soumettre()
              }}
              disabled={loading}
              className="w-full bg-vert text-white py-4 rounded-xl font-bold text-lg disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {loading ? '⏳ Envoi en cours...' : '📤 Soumettre mon signalement'}
            </button>
          </div>
        )}
      </div>
    </div>
  )
}
