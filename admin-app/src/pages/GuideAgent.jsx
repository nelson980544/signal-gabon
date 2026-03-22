export default function GuideAgent() {
  return (
    <div className="p-6 max-w-4xl">
      <h1 className="text-2xl font-bold text-gray-900 mb-2">Guide Agent CNLCEI</h1>
      <p className="text-gray-500 text-sm mb-8">Documentation complète pour le traitement des signalements SignalGabon</p>

      {/* Intro */}
      <Section>
        <InfoBox type="info">
          <strong>Rôle :</strong> Agent d'instruction — Traitement opérationnel des signalements attribués par le superviseur.
        </InfoBox>
        <p className="text-gray-700 mt-4 leading-relaxed">
          En tant qu'<strong>agent CNLCEI</strong>, vous êtes responsable du traitement opérationnel des signalements
          de corruption qui vous sont attribués par votre superviseur. Votre interface vous donne accès à vos dossiers
          en cours, vous permet de faire évoluer leur statut et de communiquer avec les citoyens concernés.
          Contrairement au superviseur, vous ne voyez que les dossiers qui vous ont été explicitement assignés.
        </p>
      </Section>

      {/* 1. Connexion */}
      <SectionTitle number="1" title="Connexion" />
      <Section>
        <h3 className="font-semibold text-gray-800 mb-3">1.1 Accéder à la plateforme</h3>
        <ol className="list-decimal list-inside text-gray-700 space-y-2 leading-relaxed">
          <li>Entrez votre <strong>adresse email institutionnelle</strong> (format <code className="bg-gray-100 px-1 rounded text-xs">prenom.nom@cnlcei.ga</code>)</li>
          <li>Entrez votre <strong>mot de passe</strong></li>
          <li>Cliquez sur <strong>Se connecter</strong></li>
        </ol>
        <p className="text-gray-600 text-sm mt-3">Vous êtes redirigé automatiquement vers votre tableau de bord personnel.</p>

        <h3 className="font-semibold text-gray-800 mt-5 mb-2">1.2 Déconnexion</h3>
        <p className="text-gray-700 mb-3 leading-relaxed">
          Cliquez sur <strong>Déconnexion</strong> en bas du menu latéral après chaque session de travail.
        </p>
        <InfoBox type="warning">
          Ne laissez jamais votre session ouverte sur un poste partagé. Déconnectez-vous systématiquement à la fin de votre session.
        </InfoBox>
      </Section>

      {/* 2. Tableau de bord */}
      <SectionTitle number="2" title="Tableau de bord" />
      <Section>
        <p className="text-gray-700 mb-4 leading-relaxed">
          Le tableau de bord vous offre une vision globale de l'activité de la plateforme.
          Les chiffres reflètent l'ensemble des signalements (tous agents confondus).
        </p>
        <h3 className="font-semibold text-gray-800 mb-3">2.1 Indicateurs clés</h3>
        <Table
          headers={['Indicateur', 'Description']}
          rows={[
            ['Total signalements', 'Nombre total de signalements reçus par la plateforme'],
            ['Non attribués', 'Dossiers en attente d\'attribution (géré par le superviseur)'],
            ['Taux de traitement', 'Pourcentage global de dossiers traités'],
            ['Délai moyen', 'Délai moyen de traitement en jours'],
          ]}
        />
        <h3 className="font-semibold text-gray-800 mt-6 mb-2">2.2 Derniers signalements</h3>
        <p className="text-gray-700 leading-relaxed">
          Le tableau affiche les dossiers les plus récents <strong>qui vous ont été attribués</strong>.
          Cliquez sur un code pour accéder directement au dossier.
        </p>
      </Section>

      {/* 3. Mes signalements */}
      <SectionTitle number="3" title="Mes signalements" />
      <Section>
        <h3 className="font-semibold text-gray-800 mb-3">3.1 Accéder à la liste</h3>
        <p className="text-gray-700 mb-3 leading-relaxed">
          Cliquez sur <strong>Signalements</strong> dans le menu latéral. La liste affiche <strong>uniquement
          les dossiers qui vous ont été attribués</strong> par votre superviseur.
        </p>
        <InfoBox type="info">
          Contrairement au superviseur, vous ne voyez pas les dossiers des autres agents.
          Cela garantit la confidentialité de l'instruction de chaque dossier.
        </InfoBox>

        <h3 className="font-semibold text-gray-800 mt-6 mb-3">3.2 Comprendre les statuts</h3>
        <Table
          headers={['Statut', 'Signification pour l\'agent']}
          rows={[
            ['🟠 Attribué', 'Le dossier vient de vous être assigné — à prendre en charge'],
            ['🟣 En instruction', 'Vous êtes en cours d\'instruction du dossier'],
            ['🟡 En examen', 'Le dossier est en révision complémentaire'],
            ['🟢 Traité', 'Vous avez clôturé le dossier positivement'],
            ['⚫ Classé', 'Dossier classé sans suite'],
          ]}
        />

        <h3 className="font-semibold text-gray-800 mt-6 mb-3">3.3 Fiche détaillée d'un dossier</h3>
        <p className="text-gray-700 mb-2 leading-relaxed">Cliquez sur un code pour ouvrir la fiche. Vous y trouverez :</p>
        <ul className="list-disc list-inside text-gray-700 space-y-1 leading-relaxed">
          <li>Code de suivi, catégorie, province et ville des faits</li>
          <li>Date des faits (déclarée par le citoyen) et date de réception</li>
          <li><strong>Description complète des faits</strong> rédigée par le citoyen</li>
          <li>Section <strong>Actions</strong> pour faire évoluer le dossier</li>
        </ul>
      </Section>

      {/* 4. Traiter un dossier */}
      <SectionTitle number="4" title="Traiter un dossier" />
      <Section>
        <h3 className="font-semibold text-gray-800 mb-4">4.1 Flux de traitement recommandé</h3>
        <div className="bg-gray-50 rounded-xl p-6 border border-gray-200 font-mono text-sm text-gray-700 mb-6">
          <div className="flex flex-col items-start space-y-1">
            {[
              { label: '1. Consulter le dossier', note: 'lire la description des faits', badge: 'bg-gray-100 text-gray-700' },
              null,
              { label: '2. En instruction', note: 'prise en charge du dossier', badge: 'bg-purple-100 text-purple-700' },
              null,
              { label: '3. Mener l\'instruction', note: 'démarches internes CNLCEI', badge: 'bg-blue-100 text-blue-700' },
              null,
              { label: '4. En examen (si besoin)', note: 'révision complémentaire', badge: 'bg-yellow-100 text-yellow-700' },
              null,
              { label: '5. Clôturer', note: '', badge: '' },
            ].map((step, i) => {
              if (step === null) return <div key={i} className="ml-4 text-gray-400">↓</div>
              if (!step.badge) return (
                <div key={i} className="flex items-center gap-3">
                  <span className="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">✅ Traité</span>
                  <span className="text-gray-400 text-xs">ou</span>
                  <span className="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">🗂️ Classé</span>
                  <span className="text-gray-400 text-xs">+ message citoyen</span>
                </div>
              )
              return (
                <div key={i} className="flex items-center gap-3">
                  <span className={`px-3 py-1 rounded-full text-xs font-semibold ${step.badge}`}>
                    {step.label}
                  </span>
                  {step.note && <span className="text-gray-400 text-xs">← {step.note}</span>}
                </div>
              )
            })}
          </div>
        </div>

        <h3 className="font-semibold text-gray-800 mb-3">4.2 Mettre à jour le statut</h3>
        <ol className="list-decimal list-inside text-gray-700 space-y-2 leading-relaxed mb-4">
          <li>Cliquez sur le menu déroulant <strong>Statut du dossier</strong></li>
          <li>Sélectionnez le nouveau statut approprié</li>
          <li>Cliquez sur <strong>Enregistrer les modifications</strong></li>
        </ol>
        <InfoBox type="tip">
          Mettez à jour le statut dès que l'état du dossier change. Cela permet au superviseur de suivre l'avancement en temps réel.
        </InfoBox>

        <h3 className="font-semibold text-gray-800 mt-6 mb-3">4.3 Communiquer avec le citoyen</h3>
        <ol className="list-decimal list-inside text-gray-700 space-y-2 leading-relaxed mb-4">
          <li>Dans la section <strong>Actions</strong>, localisez le champ <strong>Message au citoyen</strong></li>
          <li>Rédigez votre message (visible publiquement — aucune information confidentielle)</li>
          <li>Cliquez sur <strong>Enregistrer les modifications</strong></li>
        </ol>
        <InfoBox type="warning" className="mb-4">
          Ce message sera affiché tel quel au citoyen. Utilisez un langage professionnel et institutionnel.
          N'incluez jamais d'informations sensibles sur l'instruction en cours.
        </InfoBox>
        <div className="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-2">
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Exemples de messages appropriés</p>
          {[
            'Votre signalement a bien été reçu et est en cours d\'instruction. Nous vous tiendrons informé.',
            'Votre dossier a été traité. Les mesures nécessaires ont été prises conformément à nos procédures.',
            'Après examen, votre signalement a été classé. Si vous disposez d\'éléments supplémentaires, vous pouvez soumettre un nouveau signalement.',
          ].map((msg, i) => (
            <div key={i} className="flex gap-2 text-sm text-gray-700">
              <span className="text-gray-400 flex-shrink-0">•</span>
              <em>"{msg}"</em>
            </div>
          ))}
        </div>
      </Section>

      {/* 5. Statistiques */}
      <SectionTitle number="5" title="Statistiques" />
      <Section>
        <p className="text-gray-700 mb-3 leading-relaxed">
          La page <strong>Statistiques</strong> vous donne accès aux données analytiques globales de la plateforme.
        </p>
        <ul className="list-disc list-inside text-gray-700 space-y-1 leading-relaxed">
          <li><strong>KPIs</strong> : Total, Taux de traitement, Délai moyen, Non attribués</li>
          <li><strong>Par catégorie</strong> : histogramme de répartition des types de signalement</li>
          <li><strong>Par province</strong> : histogramme géographique</li>
          <li><strong>Évolution sur 6 mois</strong> : courbe chronologique</li>
          <li><strong>Signalements par agent</strong> : classement des agents — vous pouvez y situer votre performance</li>
        </ul>
      </Section>

      {/* 6. Cas d'usage */}
      <SectionTitle number="6" title="Cas d'usage typiques" />
      <Section>
        <div className="space-y-6">
          <CasUsage
            numero="1"
            titre="Prise en charge d'un nouveau dossier"
            steps={[
              'Connectez-vous à la plateforme',
              'Vérifiez le tableau de bord — un nouveau dossier "Attribué" apparaît',
              'Cliquez sur le dossier depuis "Derniers signalements"',
              'Lisez attentivement la description des faits',
              'Changez le statut à "En instruction"',
              'Envoyez un message : "Votre dossier est en cours d\'instruction."',
              'Cliquez sur Enregistrer les modifications',
            ]}
          />
          <CasUsage
            numero="2"
            titre="Clôturer un dossier traité"
            steps={[
              'Accédez au dossier en cours d\'instruction',
              'Changez le statut à "Traité"',
              'Rédigez un message de clôture pour le citoyen',
              'Cliquez sur Enregistrer les modifications',
            ]}
          />
          <CasUsage
            numero="3"
            titre="Classer un dossier sans suite"
            steps={[
              'Accédez au dossier à classer',
              'Changez le statut à "Classé"',
              'Informez le citoyen via un message explicatif',
              'Cliquez sur Enregistrer les modifications',
            ]}
          />
        </div>
      </Section>

      {/* 7. Bonnes pratiques */}
      <SectionTitle number="7" title="Bonnes pratiques" />
      <Section>
        <ul className="space-y-3">
          {[
            { icon: '📅', text: 'Traitez les dossiers par ordre de priorité : commencez par les signalements les plus anciens' },
            { icon: '🔄', text: 'Mettez à jour les statuts régulièrement pour que le superviseur ait une vue précise' },
            { icon: '💬', text: 'Rédigez des messages clairs et rassurants pour les citoyens' },
            { icon: '🔒', text: 'Respectez la confidentialité : ne discutez pas des signalements en dehors du cadre institutionnel' },
            { icon: '🚨', text: 'Signalez les dossiers urgents ou sensibles à votre superviseur si nécessaire' },
          ].map((item, i) => (
            <li key={i} className="flex items-start gap-3 text-gray-700 leading-relaxed">
              <span className="text-lg flex-shrink-0 mt-0.5">{item.icon}</span>
              <span>{item.text}</span>
            </li>
          ))}
        </ul>
      </Section>

      {/* 8. Résolution */}
      <SectionTitle number="8" title="Résolution des problèmes" />
      <Section>
        <Table
          headers={['Problème', 'Solution']}
          rows={[
            ['Aucun dossier affiché', 'Aucun dossier ne vous a encore été attribué. Contactez votre superviseur.'],
            ['Impossible de modifier le statut', 'Vérifiez que vous êtes bien sur votre dossier et que votre session est active.'],
            ['Message non enregistré', 'Cliquez bien sur "Enregistrer les modifications" après avoir rédigé le message.'],
            ['Session expirée', 'Reconnectez-vous avec vos identifiants institutionnels.'],
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

function CasUsage({ numero, titre, steps }) {
  return (
    <div className="border border-gray-200 rounded-lg overflow-hidden">
      <div className="bg-gray-50 px-4 py-2.5 border-b border-gray-200">
        <span className="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cas {numero}</span>
        <span className="ml-2 text-sm font-semibold text-gray-800">{titre}</span>
      </div>
      <ol className="p-4 space-y-2">
        {steps.map((step, i) => (
          <li key={i} className="flex items-start gap-3 text-sm text-gray-700">
            <span className="flex-shrink-0 w-5 h-5 rounded-full bg-blue-900 text-white text-xs font-bold flex items-center justify-center mt-0.5">
              {i + 1}
            </span>
            <span>{step}</span>
          </li>
        ))}
      </ol>
    </div>
  )
}
