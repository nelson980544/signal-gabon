// Infobulle accessible : visible au survol (souris) et au focus (clavier).
// L'élément enfant doit porter un aria-label si son contenu est une icône seule.
export default function Tooltip({ texte, children, className = '' }) {
  return (
    <span className={`relative group ${className || 'inline-flex'}`}>
      {children}
      <span
        role="tooltip"
        className="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 rounded-lg bg-gray-900 text-white text-xs whitespace-nowrap opacity-0 transition-opacity duration-150 group-hover:opacity-100 group-focus-within:opacity-100 z-50 shadow-lg"
      >
        {texte}
        <span className="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900" />
      </span>
    </span>
  )
}
