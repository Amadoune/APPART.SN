# Publication Review Queue Authority 01

## Décision d'autorité

L'autorité légitime de la file est **PublicationReview**.

Une candidature entre dans cette file exclusivement par un handoff événementiel indépendant à partir d'un événement certifié du Listing Lifecycle :

- `listing.publication.submitted` ;
- `listing.publication.resubmitted`, qui représente une nouvelle candidature et une nouvelle version de soumission.

Le Listing Lifecycle reste autorité de l'état et de l'événement. PublicationReview devient autorité du `QueueItem`, de son claim et de son historique de traitement. Public Projection reste un consumer aval entièrement indépendant.

## Chaîne normative

`Listing Lifecycle transition → immutable Submitted event → PublicationReview delivery → PublicationReview QueueItem`

L'ingestion est mécanique : elle ne relit pas l'Aggregate, ne décide pas de l'éligibilité et ne transforme pas la sémantique de l'événement.

## Invariants

1. Un événement de soumission produit au plus un QueueItem canonique.
2. Une resoumission produit un nouvel item grâce à sa version Lifecycle distincte.
3. PublicationReview ne réclame jamais une delivery appartenant à Public Projection.
4. Plusieurs workers peuvent partager le consumer logique PublicationReview sans créer plusieurs consumers métier.
5. Les transitions BeginReview et ApproveAndPublish restent exécutées par Listing Lifecycle.
6. La projection d'un Listing Published reste exécutée par Public Projection.

## Décision de persistance

Une persistance additive owner-scoped est nécessaire. Elle doit porter séparément :

- l'identité et l'état du QueueItem ;
- les identités de commandes de claim et leurs checksums ;
- la version optimiste ;
- les timestamps autoritatifs et de claim ;
- la delivery indépendante issue de l'événement source, si l'infrastructure de fan-out ne la matérialise pas déjà.

Une migration additive dédiée et son rollback sont nécessaires. Aucun backfill implicite n'est autorisé.

## Verdict

**GO PROPOSÉ — APPART.SN PUBLICATION REVIEW FOUNDATION v1 — PUBLICATION REVIEW QUEUE AUTHORITY 01**
