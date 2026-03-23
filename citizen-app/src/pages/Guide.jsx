import { Link } from 'react-router-dom'

const TOC = [
  { id: 'intro',       label: 'Introduction' },
  { id: 'accueil',     label: '1. Page d\'accueil' },
  { id: 'signaler',    label: '2. Faire un signalement' },
  { id: 'etape1',      label: '   Étape 1 — Catégorie' },
  { id: 'etape2',      label: '   Étape 2 — Description' },
  { id: 'etape3',      label: '   Étape 3 — Preuves' },
  { id: 'etape4',      label: '   Étape 4 — Confirmation' },
  { id: 'code',        label: '3. Code de suivi' },
  { id: 'suivi',       label: '4. Suivre mon dossier' },
  { id: 'stats',       label: '5. Statistiques publiques' },
  { id: 'conseils',    label: '6. Conseils' },
  { id: 'faq',         label: '7. Questions fréquentes' },
  { id: 'contact',     label: '8. Contact' },
]

function Section({ id, title, children }) {
  return (
    <section id={id} className="mb-12 scroll-mt-20">
      <h2 className="text-2xl font-bold text-bleu mb-4 pb-2 border-b-2 border-bleu/20">{title}</h2>
      {children}
    </section>
  )
}

function InfoBox({ type = 'info', children }) {
  const styles = {
    info:    { bg: 'bg-blue-50',   border: 'border-blue-300',  icon: 'ℹ️', text: 'text-blue-800' },
    tip:     { bg: 'bg-green-50',  border: 'border-green-300', icon: '💡', text: 'text-green-800' },
    warning: { bg: 'bg-amber-50',  border: 'border-amber-300', icon: '⚠️', text: 'text-amber-800' },
    danger:  { bg: 'bg-red-50',    border: 'border-red-300',   icon: '🔴', text: 'text-red-800' },
  }
  const s = styles[type]
  return (
    <div className={`${s.bg} border-l-4 ${s.border} rounded-r-lg p-4 mb-4 flex gap-3`}>
      <span className="text-lg flex-shrink-0">{s.icon}</span>
      <p className={`text-sm ${s.text}`}>{children}</p>
    </div>
  )
}

function StepBadge({ n }) {
  return (
    <span className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-bleu text-white font-bold text-sm flex-shrink-0">{n}</span>
  )
}

export default function Guide() {
  const scrollTo = (id) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Navbar */}
      <nav className="bg-bleu text-white px-6 py-4 flex items-center justify-between sticky top-0 z-40 shadow-md">
        <Link to="/" className="flex items-center gap-3">
          <span className="text-xl font-bold text-jaune">🛡️ SignalGabon</span>
          <span className="text-xs opacity-75 hidden sm:inline">CNLCEI</span>
        </Link>
        <div className="flex gap-4 text-sm font-medium">
          <Link to="/" className="hover:text-jaune transition hidden sm:inline">Accueil</Link>
          <Link to="/signaler" className="hover:text-jaune transition hidden sm:inline">Signaler</Link>
          <Link to="/suivi" className="hover:text-jaune transition hidden sm:inline">Suivre mon dossier</Link>
          <Link to="/guide" className="text-jaune font-semibold">Guide</Link>
        </div>
      </nav>

      {/* Header */}
      <div className="bg-gradient-to-r from-bleu to-blue-800 text-white py-10 px-6">
        <div className="max-w-5xl mx-auto">
          <div className="text-4xl mb-3">📖</div>
          <h1 className="text-3xl font-bold mb-2">Guide Utilisateur</h1>
          <p className="text-blue-200 text-sm">Comment utiliser la plateforme SignalGabon — Aucune inscription requise · Anonymat total garanti</p>
        </div>
      </div>

      <div className="max-w-5xl mx-auto px-4 py-8 flex gap-8">

        {/* Sommaire (desktop uniquement) */}
        <aside className="hidden lg:block w-64 flex-shrink-0">
          <div className="sticky top-24 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p className="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Sommaire</p>
            <nav className="space-y-1">
              {TOC.map(item => (
                <button
                  key={item.id}
                  onClick={() => scrollTo(item.id)}
                  className={`block w-full text-left text-sm px-2 py-1.5 rounded hover:bg-blue-50 hover:text-bleu transition
                    ${item.label.startsWith('   ') ? 'pl-5 text-xs text-gray-500' : 'text-gray-700 font-medium'}`}
                >
                  {item.label.trim()}
                </button>
              ))}
            </nav>
          </div>
        </aside>

        {/* Sommaire mobile */}
        <div className="lg:hidden w-full mb-6">
          <details className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <summary className="text-sm font-bold text-gray-700 cursor-pointer">📑 Sommaire</summary>
            <nav className="mt-3 space-y-1">
              {TOC.map(item => (
                <button
                  key={item.id}
                  onClick={() => scrollTo(item.id)}
                  className={`block w-full text-left text-sm px-2 py-1.5 rounded hover:bg-blue-50 hover:text-bleu transition
                    ${item.label.startsWith('   ') ? 'pl-5 text-xs text-gray-500' : 'text-gray-700'}`}
                >
                  {item.label.trim()}
                </button>
              ))}
            </nav>
          </details>
        </div>

        {/* Contenu principal */}
        <main className="flex-1 min-w-0">

          {/* Introduction */}
          <section id="intro" className="mb-12 scroll-mt-20">
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
              <p className="text-gray-700 leading-relaxed mb-4">
                <strong>SignalGabon</strong> est la plateforme officielle de signalement de la corruption au Gabon, opérée par la{' '}
                <strong>Commission Nationale de Lutte Contre la Corruption et les Infractions Économiques (CNLCEI)</strong>.
                Elle vous permet de signaler anonymement tout acte de corruption dont vous avez été témoin ou victime.
              </p>
              <p className="text-gray-700 leading-relaxed mb-4"><strong>Ce que vous pouvez faire sur SignalGabon :</strong></p>
              <ul className="space-y-2 text-sm text-gray-700">
                <li className="flex gap-2"><span className="text-vert">✓</span> Soumettre un signalement de corruption de façon totalement anonyme</li>
                <li className="flex gap-2"><span className="text-vert">✓</span> Joindre des preuves (photos, documents, enregistrements audio)</li>
                <li className="flex gap-2"><span className="text-vert">✓</span> Suivre l'état de votre dossier grâce à un code unique</li>
                <li className="flex gap-2"><span className="text-vert">✓</span> Consulter les statistiques publiques de lutte contre la corruption</li>
              </ul>
            </div>
            <InfoBox type="info">
              Votre anonymat est garanti à 100% — Aucune donnée personnelle n'est collectée. Votre identité ne peut pas être retracée à partir de votre signalement.
            </InfoBox>
          </section>

          {/* 1. Page d'accueil */}
          <Section id="accueil" title="1. Page d'accueil">
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
              <p className="text-gray-700 text-sm mb-4">Lorsque vous accédez à la plateforme, la page d'accueil vous présente :</p>
              <ul className="space-y-2 text-sm text-gray-700 mb-6">
                <li className="flex gap-2"><span className="text-bleu">→</span> Un bouton <strong>Faire un signalement</strong> pour démarrer la procédure</li>
                <li className="flex gap-2"><span className="text-bleu">→</span> Un bouton <strong>Suivre mon dossier</strong> pour consulter un signalement déjà soumis</li>
                <li className="flex gap-2"><span className="text-bleu">→</span> La section <strong>Comment ça marche ?</strong> expliquant le processus en 3 étapes</li>
                <li className="flex gap-2"><span className="text-bleu">→</span> Les <strong>Chiffres clés</strong> : nombre de signalements reçus, taux de traitement, délai moyen</li>
              </ul>
              <p className="text-sm font-semibold text-gray-700 mb-2">Menu de navigation :</p>
              <div className="grid grid-cols-2 gap-2 text-sm">
                {[
                  ['Accueil', 'Page principale'],
                  ['Signaler', 'Formulaire de signalement'],
                  ['Suivre mon dossier', 'Suivi de votre dossier'],
                  ['Statistiques', 'Statistiques publiques'],
                ].map(([lien, desc]) => (
                  <div key={lien} className="bg-gray-50 rounded-lg p-3">
                    <span className="font-medium text-bleu">{lien}</span>
                    <p className="text-gray-500 text-xs mt-0.5">{desc}</p>
                  </div>
                ))}
              </div>
            </div>
          </Section>

          {/* 2. Faire un signalement */}
          <Section id="signaler" title="2. Faire un signalement">
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
              <p className="text-gray-700 text-sm mb-4">
                Le processus se déroule en <strong>4 étapes guidées</strong>. Une barre de progression indique où vous en êtes.
              </p>
              <div className="flex items-center gap-2 mb-2">
                {[1,2,3,4].map(n => (
                  <div key={n} className="flex items-center gap-2">
                    <div className="w-8 h-8 rounded-full bg-bleu text-white flex items-center justify-center text-sm font-bold">{n}</div>
                    {n < 4 && <div className="flex-1 h-0.5 bg-bleu/20 w-8" />}
                  </div>
                ))}
              </div>
              <div className="flex gap-4 mt-1 text-xs text-gray-500">
                <span className="w-8 text-center">Catégorie</span>
                <span className="w-8 mx-2" />
                <span className="w-8 text-center">Faits</span>
                <span className="w-8 mx-2" />
                <span className="w-8 text-center">Preuves</span>
                <span className="w-8 mx-2" />
                <span className="w-8 text-center">Confirmer</span>
              </div>
            </div>

            {/* Étape 1 */}
            <div id="etape1" className="scroll-mt-20 mb-6">
              <div className="flex items-center gap-3 mb-3">
                <StepBadge n="1" />
                <h3 className="text-lg font-bold text-bleu">Étape 1 sur 4 — Catégorie</h3>
              </div>
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <p className="text-sm text-gray-700 mb-4">Sélectionnez le secteur dans lequel s'est produit l'acte de corruption.</p>
                <div className="overflow-x-auto">
                  <table className="w-full text-sm border-collapse">
                    <thead>
                      <tr className="bg-bleu text-white">
                        <th className="text-left p-3 rounded-tl-lg">Catégorie</th>
                        <th className="text-left p-3 rounded-tr-lg">Exemples de situations</th>
                      </tr>
                    </thead>
                    <tbody>
                      {[
                        ['🏛️ Administration publique', 'Demande de pot-de-vin dans les mairies, ministères, services d\'état civil'],
                        ['🚔 Police et gendarmerie', 'Corruption lors de contrôles routiers, extorsion par des agents'],
                        ['🏥 Santé et hôpitaux', 'Paiement illicite pour obtenir des soins, détournement de médicaments'],
                        ['🎓 Éducation et universités', 'Corruption dans les examens, achats de diplômes, favoritisme'],
                        ['✈️ Douanes et frontières', 'Corruption aux postes frontières, trafic facilité par des agents'],
                        ['⚖️ Justice et tribunaux', 'Corruption de magistrats, manipulation de jugements'],
                        ['📋 Marchés publics', 'Attribution frauduleuse de marchés, surfacturation, collusion'],
                        ['📦 Autre', 'Tout acte de corruption ne relevant pas des catégories précédentes'],
                      ].map(([cat, ex], i) => (
                        <tr key={i} className={i % 2 === 0 ? 'bg-gray-50' : 'bg-white'}>
                          <td className="p-3 font-medium">{cat}</td>
                          <td className="p-3 text-gray-600">{ex}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
                <div className="mt-4">
                  <InfoBox type="tip">
                    Si vous hésitez, choisissez la catégorie du secteur principal de l'institution impliquée. En cas de doute, utilisez "Autre".
                  </InfoBox>
                </div>
              </div>
            </div>

            {/* Étape 2 */}
            <div id="etape2" className="scroll-mt-20 mb-6">
              <div className="flex items-center gap-3 mb-3">
                <StepBadge n="2" />
                <h3 className="text-lg font-bold text-bleu">Étape 2 sur 4 — Description des faits</h3>
              </div>
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div className="mb-5">
                  <p className="text-sm font-semibold text-gray-800 mb-2">Description détaillée <span className="text-red-500">*</span> <span className="text-gray-400 font-normal">(minimum 50 caractères)</span></p>
                  <p className="text-sm text-gray-600 mb-3">Décrivez les faits avec précision. Incluez :</p>
                  <ul className="space-y-1 text-sm text-gray-600 ml-4">
                    <li className="flex gap-2"><span>•</span> Le lieu exact (nom du bâtiment, bureau, route...)</li>
                    <li className="flex gap-2"><span>•</span> Les personnes impliquées (leur fonction, pas nécessairement leur nom)</li>
                    <li className="flex gap-2"><span>•</span> Les montants demandés ou versés (si applicable)</li>
                    <li className="flex gap-2"><span>•</span> Les dates et horaires des faits</li>
                    <li className="flex gap-2"><span>•</span> Le contexte et les circonstances</li>
                  </ul>
                  <div className="mt-4 bg-gray-50 border-l-4 border-bleu rounded-r-lg p-4">
                    <p className="text-xs text-gray-500 mb-1 font-semibold">Exemple de description efficace :</p>
                    <p className="text-sm text-gray-700 italic">
                      "Le 15 mars 2026, au bureau des impôts de Libreville (bâtiment principal, 2e étage), un agent du guichet numéro 3 a exigé le versement de 50 000 FCFA pour traiter mon dossier de déclaration fiscale en priorité."
                    </p>
                  </div>
                </div>
                <div className="grid sm:grid-cols-3 gap-4 text-sm">
                  <div className="bg-gray-50 rounded-lg p-3">
                    <p className="font-semibold text-gray-800 mb-1">Date des faits <span className="text-red-500">*</span></p>
                    <p className="text-gray-500 text-xs">Format jj/mm/aaaa</p>
                  </div>
                  <div className="bg-gray-50 rounded-lg p-3">
                    <p className="font-semibold text-gray-800 mb-1">Province <span className="text-red-500">*</span></p>
                    <p className="text-gray-500 text-xs">Estuaire, Haut-Ogooué, Moyen-Ogooué, Ngounié, Nyanga, Ogooué-Ivindo, Ogooué-Lolo, Ogooué-Maritime, Woleu-Ntem</p>
                  </div>
                  <div className="bg-gray-50 rounded-lg p-3">
                    <p className="font-semibold text-gray-800 mb-1">Ville / Village <span className="text-red-500">*</span></p>
                    <p className="text-gray-500 text-xs">Ex. Libreville, Port-Gentil, Franceville...</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Étape 3 */}
            <div id="etape3" className="scroll-mt-20 mb-6">
              <div className="flex items-center gap-3 mb-3">
                <StepBadge n="3" />
                <h3 className="text-lg font-bold text-bleu">Étape 3 sur 4 — Preuves <span className="text-sm font-normal text-gray-400">(optionnel)</span></h3>
              </div>
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <p className="text-sm text-gray-700 mb-4">Cette étape vous permet de joindre des preuves. Elle est <strong>facultative</strong>.</p>
                <div className="grid sm:grid-cols-3 gap-4 text-sm mb-4">
                  <div className="bg-gray-50 rounded-lg p-3 text-center">
                    <div className="text-2xl mb-1">🖼️</div>
                    <p className="font-medium text-gray-700">Images</p>
                    <p className="text-xs text-gray-500">JPG, PNG</p>
                  </div>
                  <div className="bg-gray-50 rounded-lg p-3 text-center">
                    <div className="text-2xl mb-1">📄</div>
                    <p className="font-medium text-gray-700">Documents</p>
                    <p className="text-xs text-gray-500">PDF</p>
                  </div>
                  <div className="bg-gray-50 rounded-lg p-3 text-center">
                    <div className="text-2xl mb-1">🎵</div>
                    <p className="font-medium text-gray-700">Audio</p>
                    <p className="text-xs text-gray-500">MP3, WAV</p>
                  </div>
                </div>
                <p className="text-xs text-gray-500 mb-4">Maximum 5 fichiers · 10 MB par fichier</p>
                <InfoBox type="info">
                  Veillez à ce que vos fichiers ne contiennent pas de métadonnées permettant de vous identifier si vous souhaitez rester totalement anonyme.
                </InfoBox>
                <p className="text-sm text-gray-600">Si vous n'avez pas de preuves, cliquez sur <strong>Passer cette étape →</strong>.</p>
              </div>
            </div>

            {/* Étape 4 */}
            <div id="etape4" className="scroll-mt-20 mb-6">
              <div className="flex items-center gap-3 mb-3">
                <StepBadge n="4" />
                <h3 className="text-lg font-bold text-bleu">Étape 4 sur 4 — Confirmation</h3>
              </div>
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <p className="text-sm text-gray-700 mb-4">Un récapitulatif de votre signalement s'affiche : catégorie, province, ville et date des faits.</p>
                <p className="text-sm font-semibold text-gray-700 mb-3">Pour finaliser :</p>
                <ol className="space-y-2 text-sm text-gray-700">
                  <li className="flex gap-3">
                    <span className="flex-shrink-0 w-5 h-5 rounded-full bg-bleu text-white text-xs flex items-center justify-center">1</span>
                    Cochez la case <em>"Je certifie que les faits rapportés sont réels à ma connaissance et que ce signalement est fait de bonne foi."</em>
                  </li>
                  <li className="flex gap-3">
                    <span className="flex-shrink-0 w-5 h-5 rounded-full bg-bleu text-white text-xs flex items-center justify-center">2</span>
                    Cliquez sur <strong>Soumettre mon signalement</strong>
                  </li>
                </ol>
                <div className="mt-4">
                  <InfoBox type="warning">
                    Ne soumettez que des signalements dont vous avez connaissance directe. Les faux signalements nuisent au travail de la CNLCEI et aux victimes réelles de corruption.
                  </InfoBox>
                </div>
              </div>
            </div>
          </Section>

          {/* 3. Code de suivi */}
          <Section id="code" title="3. Récupérer votre code de suivi">
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
              <p className="text-sm text-gray-700 mb-4">
                Après la soumission, la plateforme vous attribue un <strong>code de suivi unique</strong>.
              </p>
              <div className="bg-bleu/5 border-2 border-bleu/20 rounded-xl p-6 text-center mb-4">
                <code className="text-2xl font-bold text-bleu tracking-widest">SG-XXXX-XXXX</code>
                <p className="text-xs text-gray-500 mt-2">Format de votre code de suivi</p>
              </div>
              <InfoBox type="danger">
                Notez précieusement ce code. C'est le seul moyen de consulter l'état de votre dossier. Sans ce code, il n'est pas possible de retrouver votre signalement (garantie d'anonymat). Il ne peut pas être récupéré en cas de perte.
              </InfoBox>
            </div>
          </Section>

          {/* 4. Suivre mon dossier */}
          <Section id="suivi" title="4. Suivre mon dossier">
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
              <div className="space-y-5">
                <div>
                  <p className="text-sm font-semibold text-gray-800 mb-1">4.1 Accéder au suivi</p>
                  <p className="text-sm text-gray-600">Cliquez sur <strong>Suivre mon dossier</strong> dans la navigation ou sur le bouton de la page d'accueil.</p>
                </div>
                <div>
                  <p className="text-sm font-semibold text-gray-800 mb-1">4.2 Saisir votre code</p>
                  <p className="text-sm text-gray-600">Entrez votre code de suivi <code className="bg-gray-100 px-2 py-0.5 rounded text-xs">SG-XXXX-XXXX</code> dans le champ et cliquez sur la loupe de recherche.</p>
                </div>
                <div>
                  <p className="text-sm font-semibold text-gray-800 mb-3">4.3 Signification des statuts</p>
                  <div className="space-y-2">
                    {[
                      { dot: 'bg-blue-500',   label: 'Reçu',          desc: 'Votre signalement a bien été enregistré, il sera traité prochainement' },
                      { dot: 'bg-yellow-400', label: 'En examen',      desc: 'La CNLCEI est en train d\'examiner votre signalement' },
                      { dot: 'bg-orange-400', label: 'Attribué',       desc: 'Votre dossier a été confié à un agent d\'instruction' },
                      { dot: 'bg-purple-500', label: 'En instruction', desc: 'Un agent instruit activement votre dossier' },
                      { dot: 'bg-vert',       label: 'Traité',         desc: 'Votre dossier a été traité — des mesures ont été prises' },
                      { dot: 'bg-gray-500',   label: 'Classé',         desc: 'Votre dossier a été classé sans suite' },
                    ].map(({ dot, label, desc }) => (
                      <div key={label} className="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
                        <span className={`mt-1 w-3 h-3 rounded-full flex-shrink-0 ${dot}`} />
                        <div>
                          <span className="text-sm font-semibold text-gray-800">{label}</span>
                          <p className="text-xs text-gray-500 mt-0.5">{desc}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </Section>

          {/* 5. Statistiques */}
          <Section id="stats" title="5. Statistiques publiques">
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
              <p className="text-sm text-gray-700 mb-4">
                La page <strong>Statistiques</strong> est accessible à tous les citoyens sans code. Elle présente :
              </p>
              <div className="grid sm:grid-cols-2 gap-3 text-sm">
                {[
                  ['📊', 'Total de signalements reçus'],
                  ['✅', 'Taux de traitement — % de dossiers clôturés'],
                  ['⏱️', 'Délai moyen de traitement en jours'],
                  ['🏷️', 'Répartition par catégorie'],
                  ['🗺️', 'Répartition par province'],
                  ['📈', 'Évolution sur 6 mois'],
                ].map(([icon, label]) => (
                  <div key={label} className="flex gap-3 p-3 bg-gray-50 rounded-lg">
                    <span>{icon}</span>
                    <span className="text-gray-700">{label}</span>
                  </div>
                ))}
              </div>
            </div>
          </Section>

          {/* 6. Conseils */}
          <Section id="conseils" title="6. Conseils pour un signalement efficace">
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <div className="space-y-4">
                {[
                  { icon: '🎯', titre: 'Soyez factuel et précis', desc: 'Décrivez les faits tels qu\'ils se sont produits, sans interprétation.' },
                  { icon: '📸', titre: 'Conservez les preuves', desc: 'Photos, enregistrements et documents renforcent considérablement votre signalement.' },
                  { icon: '⚡', titre: 'Signalez rapidement', desc: 'Plus le signalement est fait près des faits, plus il sera utile à l\'instruction.' },
                  { icon: '1️⃣', titre: 'Un signalement = un fait', desc: 'Soumettez un signalement distinct par fait. Cela facilite le traitement de chaque dossier.' },
                  { icon: '🔑', titre: 'Gardez votre code', desc: 'Conservez précieusement votre code SG-XXXX-XXXX. Il est l\'unique clé de votre dossier.' },
                ].map(({ icon, titre, desc }) => (
                  <div key={titre} className="flex gap-4 p-4 rounded-lg bg-gray-50">
                    <span className="text-2xl flex-shrink-0">{icon}</span>
                    <div>
                      <p className="font-semibold text-gray-800 text-sm">{titre}</p>
                      <p className="text-gray-600 text-sm mt-0.5">{desc}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </Section>

          {/* 7. FAQ */}
          <Section id="faq" title="7. Questions fréquentes">
            <div className="space-y-3">
              {[
                {
                  q: 'Puis-je signaler anonymement ?',
                  a: 'Oui. La plateforme ne collecte aucune donnée personnelle. Votre identité ne peut pas être retracée.',
                },
                {
                  q: 'Mon signalement sera-t-il traité ?',
                  a: 'Tous les signalements reçus sont examinés par la CNLCEI. Le délai dépend du volume de dossiers.',
                },
                {
                  q: 'Que se passe-t-il si mon dossier est classé ?',
                  a: 'Un classement sans suite signifie que le signalement n\'a pas pu être instruit (preuves insuffisantes, faits non vérifiables, etc.). Vous pouvez soumettre un nouveau signalement si vous avez des éléments supplémentaires.',
                },
                {
                  q: 'Que faire si j\'ai perdu mon code de suivi ?',
                  a: 'Le code ne peut pas être récupéré — c\'est la contrepartie de l\'anonymat total. Vous pouvez soumettre un nouveau signalement si nécessaire.',
                },
                {
                  q: 'La plateforme est-elle sécurisée ?',
                  a: 'Oui. La plateforme est opérée par la CNLCEI, institution officielle de la République Gabonaise.',
                },
              ].map(({ q, a }) => (
                <details key={q} className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
                  <summary className="p-4 cursor-pointer font-semibold text-sm text-gray-800 flex items-center justify-between hover:bg-gray-50 transition list-none">
                    <span>{q}</span>
                    <span className="text-bleu text-lg group-open:rotate-45 transition-transform">+</span>
                  </summary>
                  <div className="px-4 pb-4 text-sm text-gray-600 border-t border-gray-100 pt-3">{a}</div>
                </details>
              ))}
            </div>
          </Section>

          {/* 8. Contact */}
          <Section id="contact" title="8. Contact">
            <div className="bg-bleu text-white rounded-xl p-6">
              <div className="text-3xl mb-3">🏛️</div>
              <p className="font-bold text-lg mb-1">Commission Nationale de Lutte Contre la Corruption<br />et les Infractions Économiques</p>
              <p className="text-blue-200 text-sm">CNLCEI — République Gabonaise</p>
            </div>
          </Section>

          {/* CTA bas de page */}
          <div className="mt-8 text-center bg-vert rounded-xl p-8">
            <p className="text-white font-bold text-xl mb-2">Prêt à faire un signalement ?</p>
            <p className="text-white/80 text-sm mb-5">Anonyme · Sécurisé · En quelques minutes</p>
            <Link
              to="/signaler"
              className="inline-block bg-white text-vert font-bold px-8 py-3 rounded-xl hover:bg-gray-50 transition"
            >
              📢 Faire un signalement
            </Link>
          </div>
        </main>
      </div>

      {/* Footer */}
      <footer className="bg-bleu text-white py-8 px-6 mt-12">
        <div className="max-w-5xl mx-auto flex flex-col sm:flex-row justify-between gap-6">
          <div>
            <p className="font-bold text-jaune mb-1">🛡️ SignalGabon</p>
            <p className="text-xs text-white/60">Plateforme officielle de la CNLCEI — République Gabonaise</p>
          </div>
          <div className="flex gap-6 text-sm text-white/70">
            <Link to="/signaler" className="hover:text-white">Signaler</Link>
            <Link to="/suivi" className="hover:text-white">Suivre mon dossier</Link>
            <Link to="/stats" className="hover:text-white">Statistiques</Link>
          </div>
        </div>
        <div className="max-w-5xl mx-auto mt-6 border-t border-white/20 pt-4 text-center text-xs text-white/40">
          © 2024 CNLCEI — Tous droits réservés — République Gabonaise
        </div>
      </footer>
    </div>
  )
}
