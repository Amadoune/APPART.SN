# ADR-1009 — Public Projection Delivery Durability

- **Statut : Accepted**
- **Date : 19 juillet 2026**
- **Décision liée : ADR-1008**

## Contexte et problème

ADR-1008 adopte un Public Projection Store PostgreSQL dérivé. Les contrats 3.6A et l'Updater 3.6B sont certifiés, mais aucune liaison durable n'existe entre le commit d'un Aggregate et l'Updater. Un dispatch mémoire ou une queue écrite séparément créerait une fenêtre de perte silencieuse.

L'audit confirme : événements versionnés présents, transactions PostgreSQL locales présentes, mais aucun dispatcher, Outbox, hook post-commit, retry, quarantaine ou curseur. Les Domain Events ne sont pas persistés avec les Aggregates.

## Options évaluées

| Option | Atomicité | DDD/ownership | Reprise | Verdict |
|---|---|---|---|---|
| Outbox transactionnelle par module | même commit local | ownership source explicite | complète | **retenue** |
| Outbox centrale partagée | difficile/inter-modules | shared persistence | complète | rejetée |
| Dispatch post-commit mémoire | aucune garantie | acceptable seulement en optimisation | aucune | rejetée comme garantie |
| Polling/batch seul | durable mais tardif | scans et couplage lecture | partielle | secondaire uniquement |
| Queue externe directe | dual write | ownership transport | broker seulement | rejetée sans Outbox |
| CDC/logical decoding | atomique aux lignes | faits techniques, schéma couplé | complexe | rejeté comme primaire |
| Event store généralisé | transformation majeure | rouvre les Aggregates | complète | disproportionné |

Le tableau détaillé est dans `PUBLIC-PROJECTION-DELIVERY-DURABILITY-ANALYSIS.md`.

## Décision

Chaque module source possède une **Outbox logique locale** dans son propre schéma PostgreSQL. Toute mutation publiable persiste l'état Aggregate et ses messages Outbox dans **la même transaction locale et sur la même connexion**. Après commit, un relay livre les messages au moins une fois à des consommateurs explicitement catalogués. Le consommateur Public Projection appelle `PublicListingProjectionUpdater`.

Il n'existe aucune transaction distribuée et aucune promesse exactly-once. La garantie est **at-least-once + idempotence durable + ordre local vérifiable + réconciliation secondaire**.

Un signal mémoire post-commit pourra réveiller le relay, mais ne sera jamais la preuve de livraison. Une queue ou un broker futur sera un transport en aval de l'Outbox, jamais le second côté d'un dual write métier.

## Ownership

- le module source définit le fait public, son eventType, son payload et sa compatibilité ;
- le module source possède son journal Outbox et sa politique de rétention ;
- la frontière transactionnelle locale garantit l'atomicité ;
- l'enveloppeur Outbox calcule messageId, idempotency key et ordre ;
- le relay d'infrastructure claim, retry, met en quarantaine, marque et purge selon la politique ;
- chaque consommateur possède son marqueur idempotent et son curseur ;
- le consommateur Public Projection appelle l'Updater, sans règle métier ;
- l'Updater et le Store arbitrent readiness, watermark, obsolescence et divergence ;
- le réconciliateur détecte les écarts et déclenche replay ou rebuild ciblé.

Controllers, Views, Service Providers, repositories Aggregate, writer du Store et Store lui-même ne reçoivent aucune de ces responsabilités.

## Modèle transactionnel

La future frontière applicative spécialisée du module :

1. ouvre une transaction locale `Read Committed` ;
2. charge/mute l'Aggregate selon le cas d'usage ;
3. capture une vue stable de ses événements sans les publier ;
4. fait participer le repository existant via son abstraction de transaction injectable ;
5. écrit les enveloppes Outbox locales sur la même connexion ;
6. commit une seule fois ;
7. libère ensuite les événements de l'instance appelante.

Les repositories certifiés restent inchangés. Un futur participant transactionnel exécutera leur closure dans la transaction déjà ouverte sans commit imbriqué. Son sprint doit prouver même connexion, absence d'imbrication, rollback commun et absence de libération avant commit. Une impossibilité de le démontrer impose un arrêt et une nouvelle certification avant toute modification de repository.

Rollback métier : ni Aggregate ni message ne deviennent visibles. Crash avant commit : rollback PostgreSQL. Crash après commit : message Pending durable. Crash après effet projection et avant marquage : redelivery et résultat idempotent `AlreadyApplied`.

## Message durable

Enveloppe minimale :

- identité : `messageId`, `idempotencyKey` ;
- contrat : `eventType`, `payloadVersion`, `sourceModule` ;
- source : `aggregateType`, `aggregateId`, `aggregateVersion`, `eventIndex` ;
- traçabilité : `occurredAt` informatif, `recordedAt`, `correlationId`, `causationId` si disponibles ;
- contenu : payload minimal en liste blanche ;
- livraison : `status`, `attempts`, `availableAt`, `claimedUntil`, `deliveredAt`, `lastErrorCode`.

Le payload n'est jamais un Aggregate sérialisé. Secrets et données personnelles non indispensables sont interdits. Les erreurs journalisées sont minimisées.

`payloadVersion` est un entier positif par eventType. Ajout facultatif compatible : même version si explicitement toléré par le schéma ; changement de sens, renommage ou suppression : nouvelle version ou nouveau type. Une version inconnue est quarantinée, jamais devinée. Un type ne disparaît qu'après arrêt des producteurs, dépassement de tous les curseurs et expiration de la fenêtre de replay.

## Idempotence et ordre

La clé canonique globale est :

`sourceModule:aggregateType:aggregateId:aggregateVersion:eventIndex:eventType:payloadVersion`

Une contrainte durable interdit deux messages pour la même clé. L'ordre causal d'un Aggregate est `(aggregateVersion, eventIndex)`. Les timestamps n'ordonnent rien. Aucun ordre global inter-Aggregates n'est garanti.

- doublon identique : accusé sans second effet ;
- même clé, payload différent : divergence et quarantaine ;
- version obsolète : ne remplace rien ;
- trou : `BlockedBySequenceGap`, attente/replay/réconciliation ;
- hors ordre : différé, jamais last-write-wins ;
- watermark incomparable : divergence observable et reconstruction ciblée.

## Claiming, retry et quarantaine

Le relay revendique de petits lots avec un bail borné. Deux workers peuvent traiter des Aggregates distincts ; un seul flux ordonné est actif par Aggregate/consommateur. Un bail expiré remet le message à disposition.

Les erreurs transitoires utilisent backoff exponentiel borné avec jitter. Le nombre maximal d'essais et les délais sont configurés par consommateur et observables. Les erreurs permanentes/incompatibles vont en `Quarantined`. Les messages ne sont jamais marqués livrés avant confirmation de tous les consommateurs obligatoires, chacun avec son propre état.

États conceptuels : `Pending`, `Claimed`, `RetryScheduled`, `BlockedBySequenceGap`, `BlockedBySourceReadiness`, `Delivered`, `Quarantined`.

L'absence de version stable Geography ou Public Media produit `BlockedBySourceReadiness`. Elle ne consomme pas indéfiniment le budget de retry, ne perd pas le message et ne permet aucune promotion partielle. Une nouvelle readiness ou la réconciliation reprogramme la livraison.

La reprise manuelle est auditée et conserve messageId/idempotencyKey. Les scopes sont message, Aggregate, module, consommateur, plage de versions et high-watermark.

## Batch de réconciliation

Le batch est une défense secondaire, jamais le chemin principal de fraîcheur. Il utilise curseurs et high-watermarks durables, compare versions sources, états Outbox et watermarks du Store, puis programme des actions ciblées.

Un **delivery replay** redélivre les mêmes messages. Un **projection rebuild** relit les sources autorisées dans une génération candidate. Aucun scan ne se produit sur le chemin HTTP. La fréquence reste configurable selon le SLO ; une décision d'exploitation future la chiffrera.

## Observabilité, sécurité et rétention

Métriques obligatoires : lag versionnel et temporel, âge maximal Pending, tentatives, claims expirés, doublons, trous, blocages readiness, quarantaines, dernière version par consommateur et divergences de watermark. Les logs contiennent identifiants techniques et codes d'erreur, pas les payloads sensibles.

Les entrées Pending, bloquées ou quarantinées ne sont jamais purgées. Une entrée Delivered n'est éligible qu'après dépassement de tous les curseurs obligatoires, fermeture de la fenêtre de replay et disponibilité d'un rebuild indépendant. La durée exacte sera fixée avant exploitation.

## Conséquences

### Positives

- aucun fait accepté ne dépend d'un effet volatil ;
- frontières et ownership modulaires ;
- repositories certifiés potentiellement inchangés ;
- reprise après crash et lag mesurable ;
- mécanisme réutilisable par d'autres projections.

### Coûts et risques

- nouvelles tables locales, relay et exploitation lors de futurs sprints ;
- croissance PostgreSQL et politique de purge à tester ;
- discipline stricte de schémas de payload ;
- blocage persistant tant que Geography/Media n'ont pas de révision ;
- preuve transactionnelle indispensable autour des transactions injectables existantes.

## Rollback conceptuel

Le déploiement futur sera activable par producteur et consommateur. En rollback applicatif, les producteurs cessent d'ajouter de nouveaux types mais les lignes existantes sont conservées. Le relay peut être arrêté sans perte, puis repris. Aucun rollback ne supprime une entrée non livrée. Une projection divergente revient via génération précédente/rebuild selon ADR-1008, sans réécrire les sources métier.

## Critères d'acceptation

- état Aggregate et Outbox commit/rollback ensemble ;
- aucune publication avant commit ;
- clé déterministe et contrainte d'unicité ;
- ordre version/index démontré ;
- redelivery après crash démontrée ;
- marqueur par consommateur atomique avec son effet ;
- retry, bail, quarantaine et reprise testés ;
- readiness Geography/Media bloque sans boucle ;
- batch secondaire et aucun scan HTTP ;
- quatre repositories inchangés, ou nouvelle certification explicitement déclenchée.

## Travaux autorisés ensuite

Contrats de delivery, enveloppe et catalogue ; contrat Outbox local ; frontière transactionnelle ; migration Outbox par module ; relay/dispatcher ; consommation Updater ; retry/quarantaine ; réconciliation ; bindings et certification runtime, dans les sprints dédiés.

## Travaux toujours interdits

Dispatch mémoire comme garantie, queue directe avant commit, Outbox centrale partagée, dual write, exactly-once non prouvé, ordre par timestamp, Aggregate sérialisé, suppression d'un message non livré, logique métier dans le relay/Store/Laravel et modification silencieuse d'un repository certifié.
