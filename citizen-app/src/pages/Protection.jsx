import { Link } from 'react-router-dom'
import PageLegale, { Article, ACompleter, Encadre } from '../components/PageLegale'

export default function Protection() {
  return (
    <PageLegale
      titre="Protection des lanceurs d'alerte"
      sousTitre="Ce qui vous protège, ce qui ne vous protège pas, et ce que vous pouvez faire."
      miseAJour="octobre 2026"
    >
      <Encadre ton="prudence" titre="Lisez cette page avant de signaler">
        <p>
          Cette page dit la vérité, y compris ce que la plateforme ne sait pas
          faire. Un lanceur d'alerte qui connaît les limites se protège ; celui
          à qui on promet une sécurité totale baisse sa garde.
        </p>
      </Encadre>

      <Article numero="1" titre="Ce que la loi vous garantit">
        <p>
          La loi gabonaise de lutte contre la corruption prévoit une protection
          spéciale de l'État contre les représailles pour les lanceurs d'alerte,
          les témoins et les victimes, protection qui s'étend à leurs proches.
          Elle prévoit également la possibilité de témoigner sans que votre
          identité et votre domicile figurent au procès-verbal.
        </p>
        <p>
          Référence du texte applicable :{' '}
          <ACompleter>loi et article exacts, à confirmer au Journal officiel par le service juridique de la CNLCEI</ACompleter>
        </p>
        <Encadre ton="info">
          <p>
            Nous ne citons volontairement aucun numéro d'article tant qu'il n'a
            pas été vérifié. Une référence fausse sur une page de protection
            vaut moins que pas de référence du tout.
          </p>
        </Encadre>
        <p>
          Le Gabon a par ailleurs ratifié la Convention des Nations unies contre
          la corruption, qui impose aux États parties de protéger les personnes
          qui signalent des faits de corruption.
        </p>
      </Article>

      <Article numero="2" titre="Ce que la plateforme fait pour vous">
        <p>Ces points sont vérifiables dans le code de la plateforme, qui est public :</p>
        <ul className="list-disc pl-5 space-y-2">
          <li>
            <strong>Aucun compte n'est nécessaire</strong> pour signaler. Nous
            ne vous demandons ni nom, ni téléphone, ni pièce d'identité.
          </li>
          <li>
            <strong>Aucune adresse IP n'est enregistrée.</strong> L'application
            ne conserve pas l'adresse depuis laquelle vous vous connectez, et
            les journaux d'accès du serveur web sont désactivés.
          </li>
          <li>
            <strong>Les métadonnées de vos fichiers sont supprimées</strong>{' '}
            avant stockage : coordonnées GPS d'une photo, nom de l'auteur d'un
            document, modèle de l'appareil.
          </li>
          <li>
            <strong>Le nom d'origine de vos fichiers n'est pas conservé.</strong>{' '}
            Vos pièces deviennent « Preuve 1 », « Preuve 2 », même si votre
            fichier s'appelait autrement.
          </li>
          <li>
            <strong>L'adresse email que vous laissez, si vous en laissez une,
            est chiffrée</strong> dans la base. Elle reste illisible pour
            quiconque en obtiendrait une copie sans la clé du serveur.
          </li>
          <li>
            <strong>Seuls les agents habilités accèdent à votre dossier</strong>{' '}
            — l'agent qui en est chargé et son superviseur — et chaque
            consultation d'une pièce est enregistrée dans un journal.
          </li>
          <li>
            <strong>Les données restent au Gabon</strong>, sur un serveur
            national.
          </li>
        </ul>
      </Article>

      <Article numero="3" titre="Ce que la plateforme ne peut pas faire">
        <p>
          Aucune plateforme au monde ne peut garantir un anonymat absolu. Voici
          précisément ce qui échappe à la nôtre.
        </p>
        <ul className="list-disc pl-5 space-y-2">
          <li>
            <strong>Votre fournisseur d'accès voit que vous visitez ce site.</strong>{' '}
            Il ne voit pas ce que vous écrivez, mais il voit la connexion.
          </li>
          <li>
            <strong>Votre employeur peut surveiller son propre réseau</strong> et
            ses propres ordinateurs. S'il est l'objet de votre signalement, c'est
            le risque principal.
          </li>
          <li>
            <strong>Ce que vous écrivez peut vous désigner.</strong> Si vous
            êtes la seule personne à connaître un fait, le raconter revient à le
            signer. Nous ne pouvons rien contre cela.
          </li>
          <li>
            <strong>Une photo peut vous situer</strong> par ce qu'elle montre :
            un bureau, un badge, une écriture, un reflet.
          </li>
          <li>
            <strong>Un fichier abîmé peut échapper au nettoyage</strong> des
            métadonnées. C'est rare, mais nous préférons le dire.
          </li>
          <li>
            <strong>Le code de suivi est le seul secret de votre dossier.</strong>{' '}
            Qui l'obtient en consulte l'état. Il ne peut pas être régénéré si
            vous le perdez.
          </li>
        </ul>
      </Article>

      <Article numero="4" titre="Comment vous protéger">
        <Encadre ton="bien" titre="Avant d'envoyer">
          <ul className="list-disc pl-5 space-y-1">
            <li>Ne signalez pas depuis le réseau ni depuis le matériel de votre employeur. Utilisez votre connexion mobile personnelle.</li>
            <li>N'écrivez ni votre nom, ni celui de vos proches, ni votre fonction dans la description.</li>
            <li>Relisez votre texte en vous demandant : « combien de personnes pouvaient savoir cela ? »</li>
            <li>Si vous joignez une photo, vérifiez ce qu'elle montre en arrière-plan.</li>
            <li>Ne laissez une adresse email que si vous y tenez : le dossier se suit très bien avec le seul code.</li>
          </ul>
        </Encadre>

        <Encadre ton="bien" titre="Après l'envoi">
          <ul className="list-disc pl-5 space-y-1">
            <li>Notez le code de suivi ailleurs que sur un appareil professionnel.</li>
            <li>Effacez l'historique de navigation si l'appareil est partagé.</li>
            <li>Ne parlez pas de votre signalement sur votre lieu de travail.</li>
          </ul>
        </Encadre>

        <Encadre ton="alerte" titre="Si vous subissez des représailles">
          <p>
            Mutation soudaine, sanction disciplinaire, menace, licenciement,
            pression sur un proche : notez les dates, conservez les écrits et
            contactez la CNLCEI sans attendre.
          </p>
          <p>
            Contact : <ACompleter>téléphone, adresse email et adresse postale de la CNLCEI</ACompleter>
          </p>
          <p>
            Gardez votre code de suivi : il rattache votre démarche au dossier
            déjà ouvert.
          </p>
        </Encadre>
      </Article>

      <Article numero="5" titre="Signaler de bonne foi">
        <p>
          La protection s'adresse à ceux qui signalent de bonne foi, c'est-à-dire
          qui rapportent des faits qu'ils croient vrais. Elle ne couvre pas la
          dénonciation que l'on sait fausse, laquelle constitue un délit de
          dénonciation calomnieuse au sens du Code pénal gabonais.
        </p>
        <p>
          Se tromper de bonne foi n'est pas un délit. Mentir sciemment pour
          nuire en est un.
        </p>
        <p className="pt-2">
          Voir aussi les{' '}
          <Link to="/conditions" className="text-bleu font-medium hover:underline">conditions d'utilisation</Link>{' '}
          et la{' '}
          <Link to="/confidentialite" className="text-bleu font-medium hover:underline">politique de confidentialité</Link>.
        </p>
      </Article>
    </PageLegale>
  )
}
