# Command Signature

## Contrat candidat

```text
ListingPublicationCommandGatewayV1

beginReview(
  listingId,
  commandId,
  expectedVersion,
  actor,
  occurredAt
): ListingPublicationCommandResultV1

approveAndPublish(
  listingId,
  commandId,
  expectedVersion,
  actor,
  occurredAt
): ListingPublicationCommandResultV1
```

## Entrée minimale

- `listingId` : cible ;
- `commandId` : identité UUID canonique et intent de replay ;
- `expectedVersion` : version attendue du publication workflow ;
- `actor` : acteur déjà autorisé par la frontière appelante ;
- `occurredAt` : instant UTC canonique.

Aucun `MediaCollectionId`, PropertyId, ExpirationDate, ListingRevisionId, trigger, origin, reason ou version Aggregate n'est accepté depuis PublicationReview.

## Résultats fermés

- `Applied` ;
- `AlreadyApplied` ;
- `VersionConflict` ;
- `StateConflict` ;
- `Missing` ;
- `AuthorityUnavailable` ;
- `TransitionDenied` ;
- `DivergentCommand` ;
- `DependencyUnavailable`.

Le résultat ne contient aucune donnée Media, Property ou métier. Il peut exposer seulement les versions résultantes nécessaires à la synchronisation technique.

## Replay

Le checksum canonique couvre opération, ListingId, commandId, expectedVersion, actor et occurredAt. Le ledger appartient à Listing Lifecycle et reste distinct du ledger PublicationReview.

- commande complétée identique : `AlreadyApplied` avec résultat mémorisé ;
- même commandId divergent : `DivergentCommand` ;
- commande en cours concurrente : résultat fermé de conflit ;
- aucun fallback vers le workflow seul.
