# Contrat de commande

## Port

`PromoteAuthoredPropertyV1::promote(PromoteAuthoredPropertyCommand): PromoteAuthoredPropertyResult`.

## Entrée minimale

- `propertyId` ;
- `ownerAccountId`, fourni par l’orchestration authentifiée et vérifié contre le snapshot ;
- `expectedAuthoringVersion` ;
- `commandId` ;
- `occurredAt`.

Aucun fait métier Property n’est accepté dans la commande. Tous les faits sont relus depuis Authoring.

## Catalogue fermé

`Applied`, `AlreadyApplied`, `AuthoringMissing`, `OwnershipMismatch`, `IncompleteAuthoring`, `VersionConflict`, `DomainRejected`, `DependencyUnavailable`, `DivergentCommand`.

Les entrées UUID, la version positive et l’instant sont validés par les objets de commande. Toute indisponibilité non qualifiée est réduite à `DependencyUnavailable`.
