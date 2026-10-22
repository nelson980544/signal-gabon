# Cahier des charges — hébergement de SignalGabon au Gabon

Document à remettre à l'équipe technique de l'hébergeur (ANINF, data center
national de Nkok, ou prestataire désigné par la CNLCEI).

---

## 1. Objet

Héberger sur le territoire gabonais l'intégralité des données de la plateforme
SignalGabon : signalements de corruption, pièces jointes déposées par les
citoyens, comptes des agents de la CNLCEI et historique de traitement des
dossiers.

Les pages de l'application (HTML, JavaScript) restent sur un hébergement
international : ce sont des fichiers statiques qui ne contiennent aucune donnée.
Toute donnée nominative ou sensible est traitée et stockée au Gabon.

## 2. Pourquoi l'hébergement local

- Les signalements mettent en cause des agents publics et exposent leurs auteurs.
  Leur hébergement à l'étranger est difficilement défendable pour une institution
  de lutte contre la corruption.
- La loi n° 001/2011 relative à la protection des données à caractère personnel,
  modifiée par la loi n° 025/2023 du 12 juillet 2023, soumet les transferts hors
  du Gabon à l'autorisation de l'APDPVP. Héberger au Gabon supprime la question.

## 3. Ressources demandées

| Élément | Besoin |
|---|---|
| Machine | VM Linux, Ubuntu Server 24.04 LTS |
| Processeur | 2 vCPU |
| Mémoire | 4 Go |
| Disque | 80 Go SSD |
| Adresse IP | une IPv4 publique fixe |

Ce dimensionnement couvre plusieurs dizaines de milliers de dossiers avec leurs
pièces jointes. L'augmentation du disque est le seul point à prévoir dans le
temps, les preuves étant les fichiers les plus volumineux.

## 4. Réseau et accès

- Ports ouverts en entrée : **80** et **443** (public), **22** restreint aux
  adresses d'administration de la CNLCEI.
- Sortie autorisée : **587** (envoi des notifications email aux citoyens).
- Accès administrateur : un compte avec `sudo`, **authentification par clé SSH
  uniquement**, authentification par mot de passe désactivée.
- Nom de domaine : un sous-domaine institutionnel en `.ga`, par exemple
  `api.signalement.cnlcei.ga`, avec un enregistrement DNS A vers l'IP fournie.

## 5. Disponibilité et continuité

- Alimentation secourue : onduleur et groupe électrogène.
- Raccordement internet redondé si possible (deux opérateurs).
- Snapshot quotidien de la machine, conservé 30 jours, en plus des sauvegardes
  applicatives chiffrées produites par la plateforme elle-même.
- Délai de rétablissement visé en cas d'incident matériel : 4 heures ouvrées.

## 6. Clauses à faire figurer au contrat

1. Les données sont stockées **exclusivement sur le territoire gabonais**.
2. Aucune réplication, sauvegarde ou maintenance ne place les données hors du
   pays, y compris de façon temporaire.
3. Le contrat est soumis au **droit gabonais**.
4. L'hébergeur n'accède pas au contenu applicatif ; tout accès technique
   nécessaire est journalisé et notifié à la CNLCEI.
5. En fin de contrat, les données sont restituées dans un format exploitable et
   effacées de façon irréversible, avec attestation d'effacement.
6. Toute violation de données est notifiée à la CNLCEI sous 24 heures.

## 7. Ce que la CNLCEI installe elle-même

L'hébergeur fournit la machine ; l'application est installée et administrée par
la CNLCEI. Aucune compétence PHP n'est demandée à l'hébergeur : tout s'installe
avec Docker, selon la procédure de `docs/exploitation.md`.

## 8. Démarches à mener en parallèle

- Formalité préalable auprès de l'**APDPVP** pour le traitement.
- Réservation du sous-domaine `.ga`.
- Choix du relais SMTP pour les notifications aux citoyens. **Un relais étranger
  ferait sortir du pays l'adresse email des citoyens** : privilégier un relais
  gabonais, ou renoncer aux notifications email au profit du seul code de suivi.
