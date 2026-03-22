export default function Guide() {
  return (
    <div className="p-6 max-w-4xl">
      <h1 className="text-2xl font-bold text-gray-900 mb-2">Guide Superviseur CNLCEI</h1>
      <p className="text-gray-500 text-sm mb-8">Documentation complète de l'interface d'administration SignalGabon</p>

      {/* Intro */}
      <Section>
        <InfoBox type="info">
          <strong>Rôle :</strong> Directeur / Superviseur — Accès complet à l'administration de la plateforme SignalGabon.
        </InfoBox>
        <p className="text-gray-700 mt-4 leading-relaxed">
          La plateforme <strong>SignalGabon</strong> est l'outil officiel de la Commission Nationale de Lutte
          Contre la Corruption et les Infractions Économiques (CNLCEI) pour la réception et le traitement
          des signalements de corruption. En tant que <strong>superviseur</strong>, vous disposez d'un accès
          complet : supervision globale, attribution des dossiers, suivi statistique et gestion de l'équipe.
        </p>
      </Section>

      {/* 1. Connexion */}
      <SectionTitle number="1" title="Connexion à l'interface d'administration" />
      <Section>
        <h3 className="font-semibold text-gray-800 mb-2">1.1 Accéder à la page de connexion</h3>
        <p className="text-gray-700 mb-3 leading-relaxed">
          Naviguez vers l'adresse de l'interface d'administration. Saisissez votre adresse email
          institutionnelle et votre mot de passe, puis cliquez sur <strong>Se connecter</strong>.
        </p>
        <InfoBox type="warning">
          Ne partagez jamais vos identifiants. Chaque superviseur doit avoir son propre compte pour
          garantir la traçabilité des actions.
        </InfoBox>
        <h3 className="font-semibold text-gray-800 mt-5 mb-2">1.2 Déconnexion</h3>
        <p className="text-gray-700 leading-relaxed">
          Cliquez sur <strong>Déconnexion</strong> en bas du menu latéral gauche pour mettre fin à votre
          session de façon sécurisée.
        </p>
      </Section>

      {/* 2. Tableau de bord */}
      <SectionTitle number="2" title="Tableau de bord" />
      <Section>
        <h3 className="font-semibold text-gray-800 mb-3">2.1 Indicateurs clés (KPIs)</h3>
        <p className="text-gray-700 mb-3 leading-relaxed">
          Quatre métriques sont affichées en en-tête du tableau de bord :
        </p>
        <Table
          headers={['Indicateur', 'Description']}
          rows={[
            ['Total signalements', 'Nombre total de signalements reçus sur la plateforme'],
            ['Non attribués', 'Signalements en attente d\'attribution à un agent'],
            ['Taux de traitement', 'Pourcentage de signalements traités ou classés'],
            ['Délai moyen', 'Délai moyen de traitement en jours'],
          ]}
        />
        <InfoBox type="info" className="mt-4">
          Le nombre de signalements <strong>Non attribués</strong> doit être surveillé en priorité.
          Un chiffre élevé indique des dossiers en attente d'action.
        </InfoBox>

        <h3 className="font-semibold text-gray-800 mt-6 mb-2">2.2 Graphiques</h3>
        <ul className="list-disc list-inside text-gray-700 space-y-1 leading-relaxed">
          <li><strong>Signalements par catégorie</strong> : répartition selon les 8 catégories (Administration, Police, Santé, Éducation, Douanes, Justice, Marchés publics, Autre)</li>
          <li><strong>Évolution sur 6 mois</strong> : courbe chronologique du nombre de signalements reçus par mois</li>
        </ul>

        <h3 className="font-semibold text-gray-800 mt-6 mb-2">2.3 Derniers signalements</h3>
        <p className="text-gray-700 leading-relaxed">
          Un tableau affiche les 5 signalements les plus récents. Cliquez sur <strong>Voir tout →</strong> pour accéder à la liste complète.
        </p>
      </Section>

      {/* 3. Gestion signalements */}
      <SectionTitle number="3" title="Gestion des signalements" />
      <Section>
        <h3 className="font-semibold text-gray-800 mb-3">3.1 Les statuts disponibles</h3>
        <Table
          headers={['Statut', 'Signification']}
          rows={[
            ['🔵 Reçu', 'Signalement reçu, pas encore traité'],
            ['🟡 En examen', 'En cours d\'examen préliminaire'],
            ['🟠 Attribué', 'Assigné à un agent, en attente de traitement'],
            ['🟣 En instruction', 'Instruction en cours par l\'agent'],
            ['🟢 Traité', 'Dossier traité et clôturé positivement'],
            ['⚫ Classé', 'Dossier classé sans suite'],
          ]}
        />

        <h3 className="font-semibold text-gray-800 mt-6 mb-3">3.2 Filtrer les signalements</h3>
        <p className="text-gray-700 mb-2 leading-relaxed">
          Trois filtres sont disponibles en haut de la liste des signalements :
        </p>
        <ul className="list-disc list-inside text-gray-700 space-y-1 leading-relaxed">
          <li><strong>Statut</strong> : Reçu, En examen, Attribué, En instruction, Traité, Classé</li>
          <li><strong>Province</strong> : les 9 provinces gabonaises</li>
          <li><strong>Agent</strong> : filtrer par agent assigné</li>
        </ul>
        <p className="text-gray-600 text-sm mt-2">Cliquez sur <strong>Réinitialiser</strong> pour effacer tous les filtres.</p>

        <h3 className="font-semibold text-gray-800 mt-6 mb-3">3.3 Fiche détaillée d'un signalement</h3>
        <p className="text-gray-700 mb-3 leading-relaxed">
          Cliquez sur le code d'un signalement pour accéder à sa fiche. La section <strong>Actions</strong> vous permet de :
        </p>
        <ol className="list-decimal list-inside text-gray-700 space-y-2 leading-relaxed">
          <li><strong>Changer le statut</strong> via le menu déroulant "Statut du dossier"</li>
          <li><strong>Attribuer à un agent</strong> en sélectionnant un agent dans la liste</li>
          <li><strong>Envoyer un message au citoyen</strong> visible avec son code de suivi</li>
          <li><strong>Enregistrer</strong> en cliquant sur "Enregistrer les modifications"</li>
        </ol>
        <InfoBox type="warning" className="mt-4">
          Le message au citoyen est <strong>visible publiquement</strong>. Ne pas inclure d'informations
          confidentielles sur l'instruction du dossier.
        </InfoBox>
      </Section>

      {/* 4. Statistiques */}
      <SectionTitle number="4" title="Statistiques" />
      <Section>
        <Table
          headers={['Graphique', 'Description']}
          rows={[
            ['Par catégorie', 'Histogramme de la répartition par secteur'],
            ['Par province', 'Histogramme géographique des signalements'],
            ['Évolution 6 mois', 'Courbe chronologique des 6 derniers mois'],
            ['Par agent', 'Classement des agents par volume de dossiers'],
          ]}
        />
        <p className="text-gray-700 mt-4 leading-relaxed">
          Le graphique <strong>Signalements par agent</strong> est particulièrement utile pour équilibrer
          la charge de travail entre les agents.
        </p>
      </Section>

      {/* 5. Agents */}
      <SectionTitle number="5" title="Gestion des agents" />
      <Section>
        <p className="text-gray-700 mb-4 leading-relaxed">
          La page <strong>Agents</strong> est accessible uniquement aux superviseurs. Elle affiche la liste
          complète des agents CNLCEI avec leur nom, email institutionnel et statut (Actif / Inactif).
        </p>
        <h3 className="font-semibold text-gray-800 mb-3">Processus d'attribution recommandé</h3>
        <ol className="list-decimal list-inside text-gray-700 space-y-2 leading-relaxed">
          <li>Accédez à <strong>Signalements</strong> et filtrez par statut "Reçu"</li>
          <li>Cliquez sur le signalement à traiter</li>
          <li>Passez le statut à <strong>"Attribué"</strong></li>
          <li>Sélectionnez l'agent le plus approprié</li>
          <li>Envoyez un message au citoyen pour l'informer de la prise en charge</li>
          <li>Cliquez sur <strong>Enregistrer les modifications</strong></li>
        </ol>
      </Section>

      {/* 6. Flux */}
      <SectionTitle number="6" title="Flux de traitement d'un signalement" />
      <Section>
        <div className="bg-gray-50 rounded-xl p-6 border border-gray-200 font-mono text-sm text-gray-700">
          <div className="flex flex-col items-start space-y-1">
            {[
              { icon: '👤', label: 'Citoyen soumet', color: 'text-gray-600' },
              { arrow: true },
              { icon: '📥', label: 'Reçu', color: 'text-gray-600', badge: 'bg-gray-100 text-gray-700' },
              { arrow: true },
              { icon: '🔍', label: 'En examen', color: 'text-blue-700', badge: 'bg-blue-100 text-blue-700', note: '← Superviseur examine' },
              { arrow: true },
              { icon: '📋', label: 'Attribué', color: 'text-orange-700', badge: 'bg-orange-100 text-orange-700', note: '← Superviseur assigne un agent' },
              { arrow: true },
              { icon: '⚙️', label: 'En instruction', color: 'text-purple-700', badge: 'bg-purple-100 text-purple-700', note: '← Agent instruit le dossier' },
              { arrow: true },
            ].map((step, i) => {
              if (step.arrow) return <div key={i} className="ml-4 text-gray-400">↓</div>
              return (
                <div key={i} className="flex items-center gap-3">
                  <span className={`px-3 py-1 rounded-full text-xs font-semibold ${step.badge || ''}`}>
                    {step.icon} {step.label}
                  </span>
                  {step.note && <span className="text-gray-400 text-xs">{step.note}</span>}
                </div>
              )
            })}
            <div className="flex items-center gap-4 ml-4">
              <span className="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">✅ Traité</span>
              <span className="text-gray-400 text-xs">ou</span>
              <span className="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">🗂️ Classé</span>
            </div>
          </div>
        </div>
      </Section>

      {/* 7. Bonnes pratiques */}
      <SectionTitle number="7" title="Bonnes pratiques" />
      <Section>
        <ul className="space-y-3">
          {[
            { icon: '📅', text: 'Surveillez quotidiennement les signalements "Non attribués" pour éviter les retards' },
            { icon: '⚖️', text: 'Équilibrez la charge entre agents en consultant le graphique "Signalements par agent"' },
            { icon: '💬', text: 'Communiquez avec les citoyens : un message de suivi améliore la confiance dans la plateforme' },
            { icon: '🗂️', text: 'Classez avec discernement : un signalement classé sans suite doit être justifié en interne' },
            { icon: '🔒', text: 'Respectez l\'anonymat : ne cherchez pas à identifier les citoyens ayant soumis les signalements' },
          ].map((item, i) => (
            <li key={i} className="flex items-start gap-3 text-gray-700 leading-relaxed">
              <span className="text-lg flex-shrink-0 mt-0.5">{item.icon}</span>
              <span>{item.text}</span>
            </li>
          ))}
        </ul>
      </Section>

      {/* 8. Résolution */}
      <SectionTitle number="8" title="Résolution des problèmes courants" />
      <Section>
        <Table
          headers={['Problème', 'Solution']}
          rows={[
            ['Impossible de se connecter', 'Vérifiez email et mot de passe. Contactez l\'administrateur système si le problème persiste.'],
            ['Session expirée', 'La session expire après une période d\'inactivité. Reconnectez-vous.'],
            ['Aucun signalement affiché', 'Vérifiez les filtres actifs et cliquez sur "Réinitialiser".'],
            ['Agent introuvable dans la liste', 'L\'agent n\'est peut-être pas actif. Vérifiez la section Agents.'],
          ]}
        />
      </Section>
    </div>
  )
}

/* ── Composants utilitaires ───────────────────────────────────────────────── */

function SectionTitle({ number, title }) {
  return (
    <div className="flex items-center gap-3 mt-10 mb-0">
      <span className="flex-shrink-0 w-8 h-8 rounded-full bg-blue-900 text-white text-sm font-bold flex items-center justify-center">
        {number}
      </span>
      <h2 className="text-lg font-bold text-gray-900">{title}</h2>
    </div>
  )
}

function Section({ children }) {
  return (
    <div className="mt-4 mb-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      {children}
    </div>
  )
}

function InfoBox({ type = 'info', children, className = '' }) {
  const styles = {
    info: 'bg-blue-50 border-blue-200 text-blue-800',
    warning: 'bg-amber-50 border-amber-200 text-amber-800',
    tip: 'bg-green-50 border-green-200 text-green-800',
  }
  const icons = { info: 'ℹ️', warning: '⚠️', tip: '💡' }
  return (
    <div className={`flex gap-2 border rounded-lg p-4 text-sm leading-relaxed ${styles[type]} ${className}`}>
      <span className="flex-shrink-0">{icons[type]}</span>
      <div>{children}</div>
    </div>
  )
}

function Table({ headers, rows }) {
  return (
    <div className="overflow-hidden rounded-lg border border-gray-200">
      <table className="w-full text-sm">
        <thead className="bg-gray-50">
          <tr>
            {headers.map((h, i) => (
              <th key={i} className="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                {h}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, i) => (
            <tr key={i} className={i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'}>
              {row.map((cell, j) => (
                <td key={j} className="px-4 py-2.5 text-gray-700 border-t border-gray-100">
                  {cell}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
