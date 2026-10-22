import { useEffect, useRef } from 'react'

/**
 * Consultation d'une preuve sans quitter le dossier.
 *
 * L'URL d'objet est fournie par l'appelant (voir recupererPreuve dans api.js) ;
 * c'est aussi lui qui la libère, puisqu'il la crée.
 */
export default function VisionneusePreuve({ preuve, url, type, onFermer }) {
  const boutonFermer = useRef(null)

  useEffect(() => {
    const surTouche = e => { if (e.key === 'Escape') onFermer() }
    document.addEventListener('keydown', surTouche)
    boutonFermer.current?.focus()
    // La page du dossier ne doit pas défiler derrière la fenêtre.
    const debordement = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    return () => {
      document.removeEventListener('keydown', surTouche)
      document.body.style.overflow = debordement
    }
  }, [onFermer])

  const mime = type || preuve.type_fichier || ''

  function contenu() {
    if (mime.startsWith('image/')) {
      return (
        <img
          src={url}
          alt={`Preuve : ${preuve.nom_fichier}`}
          className="max-w-full max-h-[75vh] mx-auto object-contain"
        />
      )
    }
    if (mime === 'application/pdf') {
      return (
        <iframe
          src={url}
          title={`Preuve : ${preuve.nom_fichier}`}
          className="w-full h-[75vh] bg-white rounded-lg"
        />
      )
    }
    if (mime.startsWith('audio/')) {
      return <audio src={url} controls className="w-full mt-8 mb-8" />
    }
    if (mime.startsWith('video/')) {
      return <video src={url} controls className="max-w-full max-h-[75vh] mx-auto" />
    }
    return (
      <p className="text-center text-gray-600 text-sm py-16">
        Ce type de fichier ne peut pas être affiché ici.
        <br />
        Utilisez le bouton de téléchargement pour l'ouvrir sur votre poste.
      </p>
    )
  }

  return (
    <div
      className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4"
      onClick={onFermer}
      role="dialog"
      aria-modal="true"
      aria-label={`Preuve : ${preuve.nom_fichier}`}
    >
      <div
        className="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-full overflow-auto"
        onClick={e => e.stopPropagation()}
      >
        <div className="flex items-center gap-3 border-b px-5 py-3 sticky top-0 bg-white">
          <span className="text-sm font-medium text-gray-800 truncate flex-1">
            {preuve.nom_fichier}
          </span>
          <a
            href={url}
            download={preuve.nom_fichier}
            className="text-xs font-medium text-blue-700 hover:text-blue-900 px-3 py-1.5 rounded-lg hover:bg-blue-50"
          >
            Télécharger
          </a>
          <button
            ref={boutonFermer}
            onClick={onFermer}
            aria-label="Fermer la visionneuse"
            className="text-gray-500 hover:text-gray-800 px-2 py-1 rounded-lg hover:bg-gray-100"
          >
            ✕
          </button>
        </div>
        <div className="p-5">{contenu()}</div>
      </div>
    </div>
  )
}
