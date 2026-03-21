import { useLocation, Link } from 'react-router-dom'
import { useState } from 'react'

export default function Confirmation() {
  const { state } = useLocation()
  const code = state?.code || 'SG-XXXX-XXXX'
  const [copie, setCopie] = useState(false)

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
          className={`w-full py-3 rounded-xl font-medium mb-4 transition-all ${copie ? 'bg-vert text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`}
        >
          {copie ? '✓ Code copié dans le presse-papier !' : '📋 Copier le code'}
        </button>

        <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6 text-left">
          <p className="text-sm text-yellow-800">
            ⚠️ <strong>Conservez ce code.</strong> C'est le seul moyen de suivre votre dossier. Il n'est pas possible de le récupérer si vous le perdez.
          </p>
        </div>

        <Link
          to="/suivi"
          className="block w-full bg-bleu text-white py-3 rounded-xl font-bold hover:bg-blue-800 transition mb-3"
        >
          🔍 Suivre mon dossier →
        </Link>
        <Link to="/" className="block text-gray-500 text-sm hover:text-gray-700">
          ← Retour à l'accueil
        </Link>
      </div>
    </div>
  )
}
