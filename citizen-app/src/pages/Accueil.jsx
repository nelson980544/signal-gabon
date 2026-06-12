import { Link } from 'react-router-dom'
import { useEffect, useState } from 'react'
import api from '../api'

export default function Accueil() {
  const [stats, setStats] = useState(null)
  useEffect(() => { api.get('/stats/publiques').then(r => setStats(r.data)).catch(() => {}) }, [])

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Desktop Navbar */}
      <nav className="hidden md:flex bg-bleu text-white px-8 py-4 items-center justify-between">
        <div className="flex items-center gap-3">
          <span className="text-2xl font-bold text-jaune">🛡️ SignalGabon</span>
          <span className="text-sm opacity-75">CNLCEI</span>
        </div>
        <div className="flex gap-6 text-sm font-medium">
          <Link to="/" className="hover:text-jaune">Accueil</Link>
          <Link to="/signaler" className="hover:text-jaune">Signaler</Link>
          <Link to="/suivi" className="hover:text-jaune">Suivre mon dossier</Link>
          <Link to="/stats" className="hover:text-jaune">Statistiques</Link>
          <Link to="/guide" className="hover:text-jaune">Guide</Link>
        </div>
      </nav>

      {/* Mobile Layout */}
      <div className="md:hidden min-h-screen flex flex-col items-center justify-center p-6 bg-gradient-to-b from-bleu to-blue-800">
        <div className="text-center mb-8">
          <div className="text-6xl mb-4">🛡️</div>
          <h1 className="text-3xl font-bold text-white mb-2">SignalGabon</h1>
          <p className="text-jaune font-medium mb-4 text-sm">CNLCEI — Commission Nationale</p>
          <p className="text-white/90 text-lg font-medium px-4">Signalez la corruption. Anonymement. En toute sécurité.</p>
        </div>
        <div className="w-full max-w-sm space-y-4">
          <Link to="/signaler" title="Déclarer anonymement un fait de corruption en 4 étapes" className="block w-full bg-vert text-white text-center py-4 rounded-2xl text-lg font-bold shadow-lg">
            📢 Faire un signalement
          </Link>
          <Link to="/suivi" title="Vérifier l'avancement d'un signalement avec votre code de suivi" className="block w-full border-2 border-white text-white text-center py-4 rounded-2xl text-lg font-bold">
            🔍 Suivre mon dossier
          </Link>
          <Link to="/stats" title="Consulter les chiffres publics de la lutte anti-corruption" className="block w-full border border-white/50 text-white/80 text-center py-3 rounded-xl text-sm">
            📊 Statistiques publiques
          </Link>
          <Link to="/guide" title="Apprendre à utiliser la plateforme pas à pas" className="block w-full border border-white/30 text-white/60 text-center py-3 rounded-xl text-sm">
            📖 Guide utilisateur
          </Link>
        </div>
        <div className="mt-8 bg-white/10 rounded-xl p-4 text-center max-w-sm w-full">
          <p className="text-white text-sm">🔒 <strong>Votre anonymat est garanti à 100%</strong></p>
          <p className="text-white/70 text-xs mt-1">Aucune donnée personnelle collectée</p>
        </div>
      </div>

      {/* Desktop Layout */}
      <div className="hidden md:block">
        {/* Hero */}
        <section className="bg-gradient-to-r from-bleu to-blue-800 text-white py-24 px-8">
          <div className="max-w-4xl mx-auto text-center">
            <div className="text-7xl mb-6">🛡️</div>
            <h1 className="text-5xl font-bold mb-4">SignalGabon</h1>
            <p className="text-xl text-jaune font-semibold mb-4">Plateforme officielle de signalement de la corruption</p>
            <p className="text-lg text-white/80 mb-10 max-w-2xl mx-auto">
              Signalez la corruption anonymement et en toute sécurité. Votre signalement aide à construire un Gabon plus juste.
            </p>
            <div className="flex gap-4 justify-center">
              <Link to="/signaler" title="Déclarer anonymement un fait de corruption en 4 étapes" className="bg-vert text-white px-8 py-4 rounded-xl text-lg font-bold hover:bg-green-600 transition">
                📢 Faire un signalement
              </Link>
              <Link to="/suivi" title="Vérifier l'avancement d'un signalement avec votre code de suivi" className="border-2 border-white text-white px-8 py-4 rounded-xl text-lg font-bold hover:bg-white/10 transition">
                🔍 Suivre mon dossier
              </Link>
            </div>
          </div>
        </section>

        {/* Comment ça marche */}
        <section className="py-16 px-8 bg-white">
          <div className="max-w-4xl mx-auto">
            <h2 className="text-3xl font-bold text-center text-bleu mb-12">Comment ça marche ?</h2>
            <div className="grid grid-cols-3 gap-8">
              {[
                { icon: '📝', titre: 'Décrivez les faits', desc: 'Remplissez le formulaire en détaillant les faits de corruption observés. Restez factuel.' },
                { icon: '🔒', titre: 'Soumettez anonymement', desc: 'Aucune donnée personnelle n\'est collectée. Votre identité reste totalement protégée.' },
                { icon: '📊', titre: 'Nous agissons', desc: 'La CNLCEI examine votre signalement et prend les mesures nécessaires pour y donner suite.' }
              ].map((e, i) => (
                <div key={i} className="text-center p-6 rounded-xl border border-gray-100 shadow-sm">
                  <div className="text-5xl mb-4">{e.icon}</div>
                  <h3 className="font-bold text-lg text-bleu mb-2">{e.titre}</h3>
                  <p className="text-gray-600 text-sm">{e.desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Chiffres clés */}
        {stats && (
          <section className="py-16 px-8 bg-gray-50">
            <div className="max-w-4xl mx-auto">
              <h2 className="text-3xl font-bold text-center text-bleu mb-12">Chiffres clés</h2>
              <div className="grid grid-cols-3 gap-6">
                <div className="bg-white rounded-xl p-6 text-center shadow-sm border-t-4 border-vert">
                  <div className="text-4xl font-bold text-vert">{stats.total}</div>
                  <div className="text-gray-600 mt-1">Signalements reçus</div>
                </div>
                <div className="bg-white rounded-xl p-6 text-center shadow-sm border-t-4 border-bleu">
                  <div className="text-4xl font-bold text-bleu">{stats.taux_traitement}%</div>
                  <div className="text-gray-600 mt-1">Taux de traitement</div>
                </div>
                <div className="bg-white rounded-xl p-6 text-center shadow-sm border-t-4 border-jaune">
                  <div className="text-4xl font-bold text-yellow-500">{stats.delai_moyen}j</div>
                  <div className="text-gray-600 mt-1">Délai moyen de traitement</div>
                </div>
              </div>
            </div>
          </section>
        )}

        {/* Bannière sécurité */}
        <section className="py-8 px-8 bg-vert">
          <div className="max-w-4xl mx-auto text-center text-white">
            <p className="text-xl font-bold">🔒 Votre anonymat est garanti à 100%</p>
            <p className="text-white/80 mt-1">Aucune donnée personnelle n'est collectée côté citoyen. Votre identité ne peut pas être retracée.</p>
          </div>
        </section>

        {/* Footer */}
        <footer className="bg-bleu text-white py-10 px-8">
          <div className="max-w-4xl mx-auto">
            <div className="grid grid-cols-3 gap-8 mb-8">
              <div>
                <h3 className="font-bold text-jaune mb-3">SignalGabon</h3>
                <p className="text-sm text-white/70">Plateforme officielle de la Commission Nationale de Lutte Contre la Corruption et les Infractions Économiques.</p>
              </div>
              <div>
                <h3 className="font-bold text-jaune mb-3">Navigation</h3>
                <div className="space-y-1 text-sm text-white/70">
                  <Link to="/signaler" className="block hover:text-white">Faire un signalement</Link>
                  <Link to="/suivi" className="block hover:text-white">Suivre un dossier</Link>
                  <Link to="/stats" className="block hover:text-white">Statistiques publiques</Link>
                  <Link to="/guide" className="block hover:text-white">Guide utilisateur</Link>
                </div>
              </div>
              <div>
                <h3 className="font-bold text-jaune mb-3">CNLCEI</h3>
                <p className="text-sm text-white/70">Commission Nationale de Lutte Contre la Corruption et les Infractions Économiques — République Gabonaise</p>
              </div>
            </div>
            <div className="border-t border-white/20 pt-4 text-center text-xs text-white/50">
              © 2024 CNLCEI — Tous droits réservés — République Gabonaise
            </div>
          </div>
        </footer>
      </div>
    </div>
  )
}
