import { Link } from 'react-router-dom'
import PageLegale, { Article, ACompleter, Encadre } from '../components/PageLegale'

export default function Conditions() {
  return (
    <PageLegale
      titre="Conditions d'utilisation"
      sousTitre="Ce que vous pouvez attendre de la plateforme, et ce qu'elle attend de vous."
      miseAJour="octobre 2026"
    >
      <Article numero="1" titre="Objet du service">
        <p>
          SignalGabon est la plateforme officielle de signalement de la
          corruption mise à disposition par la{' '}
          <ACompleter>dénomination officielle exacte de la CNLCEI</ACompleter>.
          Elle permet à toute personne de signaler des faits de corruption ou
          d'infractions assimilées, et d'en suivre le traitement au moyen d'un
          code de suivi.
        </p>
        <p>
          Son usage est gratuit et ne nécessite pas de compte.
        </p>
      </Article>

      <Article numero="2" titre="Ce que vous pouvez signaler">
        <p>
          Des faits de corruption, de détournement, d'extorsion, de favoritisme
          dans les marchés publics ou d'abus de fonction, dont vous avez été
          témoin ou victime.
        </p>
        <p>La plateforme n'est pas adaptée aux situations suivantes :</p>
        <ul className="list-disc pl-5 space-y-1">
          <li>Une urgence ou un danger immédiat — contactez les services de secours.</li>
          <li>Un litige privé sans lien avec un agent ou un service public.</li>
          <li>Une réclamation administrative ordinaire.</li>
        </ul>
      </Article>

      <Article numero="3" titre="Votre engagement de bonne foi">
        <p>
          En envoyant un signalement, vous certifiez que les faits rapportés
          sont réels à votre connaissance et que votre démarche est faite de
          bonne foi.
        </p>
        <p>
          La bonne foi n'est pas la certitude. Vous pouvez vous tromper, ou ne
          connaître qu'une partie des faits : cela ne vous expose à rien. Ce qui
          est interdit, c'est de dénoncer des faits que vous savez faux.
        </p>

        <Encadre ton="alerte" titre="Dénonciation calomnieuse">
          <p>
            Dénoncer une personne en sachant que les faits sont totalement ou
            partiellement inexacts constitue le délit de dénonciation
            calomnieuse, réprimé par l'article 282 du Code pénal gabonais. La
            personne mise en cause peut par ailleurs demander réparation.
          </p>
          <p>
            Référence et peines applicables :{' '}
            <ACompleter>version en vigueur de l'article 282 et peines, à vérifier au Journal officiel</ACompleter>
          </p>
        </Encadre>
      </Article>

      <Article numero="4" titre="Ce que fait la CNLCEI de votre signalement">
        <p>
          Chaque signalement est enregistré, examiné, puis attribué à un agent
          qui en conduit l'instruction. Le statut de votre dossier évolue au fil
          de ce traitement et reste consultable avec votre code de suivi.
        </p>
        <p>
          La CNLCEI apprécie seule les suites à donner. L'envoi d'un signalement
          ne garantit ni l'ouverture d'une enquête, ni des poursuites, ni un
          délai de traitement déterminé. Un dossier peut être classé sans suite,
          notamment faute d'éléments suffisants.
        </p>
        <p>
          Les messages que l'agent vous adresse dans le suivi sont publics pour
          qui détient le code : n'y faites pas figurer d'information que vous ne
          voudriez pas voir lue par un tiers qui obtiendrait ce code.
        </p>
      </Article>

      <Article numero="5" titre="Le code de suivi">
        <p>
          Le code au format <code className="bg-gray-100 px-1.5 py-0.5 rounded text-sm">SG-XXXX-XXXX</code>{' '}
          remis à l'envoi est le seul moyen d'accéder à l'état de votre dossier.
        </p>
        <ul className="list-disc pl-5 space-y-1">
          <li>Il ne peut pas être régénéré ni retrouvé : conservez-le.</li>
          <li>Toute personne qui l'obtient peut consulter l'état du dossier.</li>
          <li>Perdre le code n'efface pas le signalement, qui poursuit son instruction.</li>
        </ul>
      </Article>

      <Article numero="6" titre="Pièces jointes">
        <p>
          Vous pouvez joindre jusqu'à 10 pièces par dossier, de 10 Mo au
          maximum chacune, aux formats image, PDF, audio et vidéo. Leurs
          métadonnées sont supprimées et leur nom d'origine n'est pas conservé.
        </p>
        <p>
          N'envoyez que des pièces que vous détenez légitimement. La plateforme
          n'est pas un canal d'exfiltration de documents classifiés.
        </p>
      </Article>

      <Article numero="7" titre="Usages interdits">
        <ul className="list-disc pl-5 space-y-1">
          <li>Envoyer des signalements en masse ou automatisés.</li>
          <li>Tenter d'accéder à des dossiers dont vous n'avez pas le code.</li>
          <li>Déposer des contenus injurieux, diffamatoires ou sans rapport avec un fait de corruption.</li>
          <li>Perturber le fonctionnement du service.</li>
        </ul>
        <p>
          Le nombre d'envois par période est limité techniquement afin de
          préserver la disponibilité du service.
        </p>
      </Article>

      <Article numero="8" titre="Disponibilité">
        <p>
          La CNLCEI s'efforce d'assurer la continuité du service, sans pouvoir
          la garantir : une interruption peut survenir pour maintenance ou pour
          une cause extérieure. Une indisponibilité n'efface aucun dossier déjà
          enregistré.
        </p>
      </Article>

      <Article numero="9" titre="Données personnelles">
        <p>
          Le traitement de vos données est décrit dans la{' '}
          <Link to="/confidentialite" className="text-bleu font-medium hover:underline">
            politique de confidentialité
          </Link>
          . Les mesures de protection, et leurs limites, sont détaillées dans la
          page{' '}
          <Link to="/protection" className="text-bleu font-medium hover:underline">
            protection des lanceurs d'alerte
          </Link>
          .
        </p>
      </Article>

      <Article numero="10" titre="Droit applicable">
        <p>
          Les présentes conditions sont régies par le droit gabonais. Tout
          litige relève de la compétence des juridictions gabonaises.
        </p>
      </Article>
    </PageLegale>
  )
}
