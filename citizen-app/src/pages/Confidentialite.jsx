import { Link } from 'react-router-dom'
import PageLegale, { Article, ACompleter, Encadre } from '../components/PageLegale'

export default function Confidentialite() {
  return (
    <PageLegale
      titre="Politique de confidentialité"
      sousTitre="Quelles données nous traitons, pourquoi, combien de temps, et quels sont vos droits."
      miseAJour="octobre 2026"
    >
      <Article numero="1" titre="Qui traite vos données">
        <p>
          Le responsable du traitement est la{' '}
          <ACompleter>dénomination officielle exacte de la CNLCEI</ACompleter>,
          dont le siège est situé{' '}
          <ACompleter>adresse postale</ACompleter>.
        </p>
        <p>
          Contact pour toute question relative à vos données :{' '}
          <ACompleter>adresse email et téléphone du délégué à la protection des données</ACompleter>
        </p>
        <p>
          Le traitement est déclaré auprès de l'Autorité pour la protection des
          données personnelles et de la vie privée (APDPVP) sous le numéro{' '}
          <ACompleter>numéro de déclaration ou d'autorisation</ACompleter>.
        </p>
      </Article>

      <Article numero="2" titre="Les données que nous collectons">
        <p>Un signalement se dépose sans compte et sans identité. Nous enregistrons :</p>
        <div className="overflow-x-auto">
          <table className="w-full text-sm border-collapse my-2">
            <thead>
              <tr className="border-b">
                <th className="text-left py-2 pr-4 font-semibold text-gray-600">Donnée</th>
                <th className="text-left py-2 font-semibold text-gray-600">Précision</th>
              </tr>
            </thead>
            <tbody className="align-top">
              <tr className="border-b">
                <td className="py-2 pr-4 font-medium">Catégorie, province, ville, date des faits</td>
                <td className="py-2">Choisies par vous dans le formulaire</td>
              </tr>
              <tr className="border-b">
                <td className="py-2 pr-4 font-medium">Description des faits</td>
                <td className="py-2">Texte libre. Son contenu dépend entièrement de vous</td>
              </tr>
              <tr className="border-b">
                <td className="py-2 pr-4 font-medium">Pièces jointes</td>
                <td className="py-2">Métadonnées supprimées, nom d'origine remplacé par « Preuve 1 », « Preuve 2 »…</td>
              </tr>
              <tr className="border-b">
                <td className="py-2 pr-4 font-medium">Adresse email</td>
                <td className="py-2"><strong>Facultative.</strong> Uniquement si vous souhaitez être averti des changements de statut. Chiffrée en base</td>
              </tr>
              <tr>
                <td className="py-2 pr-4 font-medium">Code de suivi</td>
                <td className="py-2">Généré au hasard, sans lien avec vous</td>
              </tr>
            </tbody>
          </table>
        </div>

        <Encadre ton="bien" titre="Ce que nous ne collectons pas">
          <p>
            Ni votre nom, ni votre téléphone, ni votre pièce d'identité, ni
            votre adresse IP, ni aucun traceur publicitaire. La plateforme
            n'utilise pas de cookie de mesure d'audience.
          </p>
        </Encadre>
      </Article>

      <Article numero="3" titre="Pourquoi nous les traitons">
        <p>
          Ces données servent exclusivement à l'exercice des missions légales de
          la CNLCEI : recevoir, examiner et instruire les signalements de
          corruption et d'infractions assimilées, et le cas échéant transmettre
          le dossier aux autorités compétentes.
        </p>
        <p>
          Elles alimentent aussi les statistiques publiques du site, qui ne
          portent que sur des totaux par catégorie, par province et par statut,
          sans aucun élément permettant d'identifier un dossier ou une personne.
        </p>
        <p>
          Elles ne sont jamais utilisées à des fins commerciales, ni cédées à
          des tiers.
        </p>
      </Article>

      <Article numero="4" titre="Qui y a accès">
        <ul className="list-disc pl-5 space-y-2">
          <li>L'agent de la CNLCEI chargé du dossier, et lui seul parmi les agents.</li>
          <li>Le superviseur, qui attribue les dossiers et en suit le traitement.</li>
          <li>Les autorités judiciaires, lorsque la loi l'exige.</li>
        </ul>
        <p>
          Chaque consultation d'une pièce jointe est enregistrée dans un journal
          technique indiquant quel agent a ouvert quelle pièce et quand.
        </p>
      </Article>

      <Article numero="5" titre="Où sont hébergées vos données">
        <p>
          Les signalements, les pièces jointes et les comptes des agents sont
          hébergés sur un serveur situé <strong>au Gabon</strong>. Aucune de ces
          données n'est transférée hors du territoire national.
        </p>
        <p>
          Les sauvegardes sont chiffrées et restent elles aussi au Gabon.
        </p>
      </Article>

      <Article numero="6" titre="Combien de temps nous les conservons">
        <p>
          Un dossier est conservé pendant toute son instruction. Une fois clos
          — traité ou classé sans suite — il est conservé{' '}
          <ACompleter>durée de conservation retenue par la CNLCEI, en mois ou en années</ACompleter>,
          puis supprimé définitivement, pièces jointes comprises.
        </p>
        <p>
          Les comptes des agents sont conservés le temps de leurs fonctions.
        </p>
      </Article>

      <Article numero="7" titre="Vos droits">
        <p>
          La loi n° 001/2011 relative à la protection des données à caractère
          personnel, modifiée par la loi n° 025/2023, vous reconnaît un droit
          d'accès, de rectification, d'effacement, d'opposition et de limitation
          du traitement.
        </p>
        <Encadre ton="prudence" titre="Une limite tient à l'anonymat lui-même">
          <p>
            Comme nous ne savons pas qui vous êtes, nous ne pouvons pas relier
            un dossier à une personne. Pour exercer un droit sur un signalement,
            il faut nous communiquer son <strong>code de suivi</strong> : c'est
            le seul élément qui nous permette de retrouver le dossier concerné.
          </p>
          <p>
            C'est la contrepartie directe de la protection que nous vous
            offrons.
          </p>
        </Encadre>
        <p>
          Adressez votre demande à{' '}
          <ACompleter>adresse email dédiée aux demandes relatives aux données</ACompleter>.
          Si la réponse ne vous satisfait pas, vous pouvez saisir l'APDPVP.
        </p>
      </Article>

      <Article numero="8" titre="Sécurité">
        <p>
          Les échanges avec le site sont chiffrés. L'adresse email de contact
          est chiffrée en base. Les pièces jointes ne sont accessibles qu'aux
          agents habilités, après authentification, et jamais par une adresse
          publique.
        </p>
        <p>
          Aucune mesure n'étant infaillible, nous vous invitons à lire la page{' '}
          <Link to="/protection" className="text-bleu font-medium hover:underline">
            protection des lanceurs d'alerte
          </Link>
          , qui détaille aussi ce que la plateforme ne peut pas garantir.
        </p>
      </Article>

      <Article numero="9" titre="Modifications">
        <p>
          Toute modification de cette politique sera publiée sur cette page,
          avec sa date de mise à jour.
        </p>
      </Article>
    </PageLegale>
  )
}
