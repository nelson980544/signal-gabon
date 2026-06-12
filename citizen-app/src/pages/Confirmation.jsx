import { useLocation, Link } from 'react-router-dom'
import { useState, useEffect } from 'react'

const STORAGE_KEY = 'signalGabon_codes'

export default function Confirmation() {
  const { state } = useLocation()
  const code = state?.code || 'SG-XXXX-XXXX'
  const [copie, setCopie] = useState(false)
  const [anciensCodes, setAnciensCodes] = useState([])

  // Sauvegarder le code dès l'affichage de la page
  useEffect(() => {
    if (!state?.code) return
    const existing = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
    // Éviter les doublons et limiter à 5 codes
    const updated = [state.code, ...existing.filter(c => c !== state.code)].slice(0, 5)
    localStorage.setItem(STORAGE_KEY, JSON.stringify(updated))
    setAnciensCodes(updated.filter(c => c !== state.code))
  }, [state?.code])

  const copier = () => {
    navigator.clipboard.writeText(code).then(() => {
      setCopie(true)
      setTimeout(() => setCopie(false), 2500)
    })
  }

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full text-center">
        <div className="text-7xl mb-4">✅</div>
        <h1 className="text-2xl font-bold text-vert mb-2">Signalement enregistré !</h1>
        <p className="text-gray-600 mb-8 text-sm leading-relaxed">
          Votre signalement a été transmis à la CNLCEI avec succès. Conservez précieusement ce code pour suivre l'avancement de votre dossier.
        </p>

        <div className="bg-bleu/5 border-2 border-bleu rounded-2xl p-5 mb-5">
          <div className="text-xs text-gray-500 mb-2 uppercase tracking-wider font-medium">Votre code de suivi</div>
          <div className="text-3xl font-mono font-bold text-bleu tracking-widest">{code}</div>
        </div>

        <button
          onClick={copier}
          title="Copier le code de suivi dans le presse-papier"
          className={`w-full py-3 rounded-xl font-medium mb-4 transition-all ${copie ? 'bg-vert text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`}
        >
          {copie ? '✓ Code copié dans le presse-papier !' : '📋 Copier le code'}
        </button>

        <div className="bg-green-50 border border-green-200 rounded-xl p-4 mb-4 text-left">
          <p className="text-sm text-green-800">
            💾 <strong>Code sauvegardé automatiquement</strong> sur cet appareil. Vous pourrez le retrouver sur la page "Suivre mon dossier".
          </p>
        </div>

        <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6 text-left">
          <p className="text-sm text-yellow-800">
            ⚠️ <strong>Notez aussi ce code.</strong> La sauvegarde locale peut être effacée si vous videz votre navigateur.
          </p>
        </div>

        {anciensCodes.length > 0 && (
          <div className="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6 text-left">
            <p className="text-xs font-semibold text-gray-500 mb-2">📂 Vos dossiers précédents</p>
            <div className="space-y-1">
              {anciensCodes.map(c => (
                <Link key={c} to="/suivi" state={{ code: c }} className="block font-mono text-sm text-bleu hover:underline">
                  {c}
                </Link>
              ))}
            </div>
          </div>
        )}

        <Link
          to="/suivi"
          title="Consulter l'avancement de votre dossier avec ce code"
          className="block w-full bg-bleu text-white py-3 rounded-xl font-bold hover:bg-blue-800 transition mb-3"
        >
          🔍 Suivre mon dossier →
        </Link>
        <Link to="/" title="Revenir à la page d'accueil" className="block text-gray-500 text-sm hover:text-gray-700">
          ← Retour à l'accueil
        </Link>
      </div>
    </div>
  )
}

