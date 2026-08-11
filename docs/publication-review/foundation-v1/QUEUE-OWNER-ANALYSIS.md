# Queue Owner Analysis

## Comparaison

| Option | Ownership | Idempotence / replay | Concurrence / isolation | Compatibilité | Décision |
|---|---|---|---|---|---|
| A — Réutiliser Lifecycle | Lifecycle devrait énumérer et réclamer des travaux PublicationReview | imposerait un ledger étranger au Lifecycle | étendrait son store et ses verrous | modifie une frontière certifiée | Rejetée |
| B — Réutiliser Projection Outbox | delivery possédée par `public-projection-updater` | le replay Review serait couplé au replay Projection | un claim Review pourrait bloquer Projection | détruit l'indépendance aval | Rejetée |
| C — Handoff événementiel indépendant | Lifecycle possède l'événement ; PublicationReview possède sa delivery et sa queue | identités canoniques séparées, replay local | transaction et locks locaux à PublicationReview | additive, sans modification sémantique | Retenue |
| D — Scan périodique ou SQL direct | ownership ambigu | déduplication artificielle | races entre scan et transitions | contourne les contrats | Rejetée |

## Répartition d'autorité

| Élément | Owner |
|---|---|
| état `Submitted` et version de transition | Listing Lifecycle |
| événement immutable de soumission | Listing Lifecycle |
| delivery destinée à la revue | PublicationReview |
| QueueItem et claim | PublicationReview |
| identité/autorisation de l'acteur | IAM, consommée ultérieurement par contrat |
| BeginReview / ApproveAndPublish | Listing Lifecycle |
| projection du Published | Public Projection |
| index Search | Search |

## Consumer

La décision retient **un consumer logique unique PublicationReview**. Plusieurs instances ou workers peuvent le servir avec `FOR UPDATE SKIP LOCKED` ou une garantie concurrente équivalente. Créer un consumer par worker fragmenterait le traitement et dupliquerait les deliveries.

Public Projection conserve son propre consumer, son propre curseur, ses propres retries et ses propres claims.

## Risques bornés

- livraison dupliquée : absorbée par l'identité canonique du QueueItem ;
- crash après insertion : replay `AlreadyApplied` ;
- workers concurrents : claim atomique et version optimiste ;
- événement hors ordre : contrôle de version par Listing et rejet/quarantaine explicite ;
- indisponibilité PublicationReview : n'empêche pas la delivery Projection ;
- indisponibilité Projection : n'empêche pas l'ingestion Review.
