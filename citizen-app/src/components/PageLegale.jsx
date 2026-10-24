import { Link } from 'react-router-dom'

/**
 * Mise en page commune aux trois documents publics : confidentialité,
 * conditions d'utilisation et protection des lanceurs d'alerte.
 */
export default function PageLegale({ titre, sousTitre, miseAJour, children }) {
  return (
    // text-left : #root centre tout le texte par défaut (src/index.css), ce qui
    // rend un document long illisible. On ne corrige que cette page.
    <div className="min-h-screen bg-gray-50 text-left">
      <header className="bg-bleu text-white">
        <div className="max-w-3xl mx-auto px-6 py-8">
          <Link to="/" className="text-white/70 hover:text-white text-sm">← Retour à l'accueil</Link>
          <h1 className="text-3xl font-bold mt-4">{titre}</h1>
          {sousTitre && <p className="text-white/80 mt-2">{sousTitre}</p>}
          {miseAJour && (
            <p className="text-white/50 text-xs mt-4">Dernière mise à jour : {miseAJour}</p>
          )}
        </div>
      </header>

      <main className="max-w-3xl mx-auto px-6 py-10">
        <div className="bg-white rounded-2xl shadow-sm p-7 sm:p-10">{children}</div>

        <nav className="flex flex-wrap gap-x-5 gap-y-2 justify-center mt-8 text-sm text-gray-500">
          <Link to="/confidentialite" className="hover:text-bleu">Confidentialité</Link>
          <Link to="/conditions" className="hover:text-bleu">Conditions d'utilisation</Link>
          <Link to="/protection" className="hover:text-bleu">Protection des lanceurs d'alerte</Link>
          <Link to="/guide" className="hover:text-bleu">Guide</Link>
        </nav>
      </main>
    </div>
  )
}

export function Article({ numero, titre, children }) {
  return (
    <section className="mb-9 last:mb-0">
      <h2 className="text-lg font-bold text-bleu mb-3">
        {numero && <span className="text-gray-400 mr-2">{numero}.</span>}
        {titre}
      </h2>
      <div className="space-y-3 text-gray-700 leading-relaxed">{children}</div>
    </section>
  )
}

/**
 * Information que la CNLCEI doit fournir avant la mise en ligne. Visible
 * exprès : une page légale incomplète ne doit pas pouvoir passer inaperçue.
 */
export function ACompleter({ children }) {
  return (
    <mark className="bg-jaune/30 text-yellow-900 border border-jaune/60 rounded px-1.5 py-0.5 text-sm font-medium">
      [À COMPLÉTER : {children}]
    </mark>
  )
}

export function Encadre({ ton = 'info', titre, children }) {
  const styles = {
    info: 'bg-blue-50 border-blue-200 text-blue-900',
    bien: 'bg-green-50 border-green-200 text-green-900',
    alerte: 'bg-red-50 border-red-200 text-red-900',
    prudence: 'bg-amber-50 border-amber-200 text-amber-900',
  }

  return (
    <div className={`border rounded-xl p-4 my-4 ${styles[ton]}`}>
      {titre && <p className="font-bold text-sm mb-1.5">{titre}</p>}
      <div className="text-sm space-y-2 leading-relaxed">{children}</div>
    </div>
  )
}
