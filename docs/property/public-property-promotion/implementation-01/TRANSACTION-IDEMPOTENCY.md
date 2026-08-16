# Transaction et idempotence

## Frontière transactionnelle

`PostgreSqlPromotionTransaction` ouvre une transaction locale RealEstateCatalog. Des advisory locks déterministes protègent `commandId`, `propertyId` et le snapshot Authoring. `PostgreSqlPromotionParticipantTransaction` permet au registre Property de participer à cette transaction sans l’ouvrir ni la valider séparément.

Le write Property et le ledger sont validés ensemble. Une erreur du ledger provoque le rollback de l’Aggregate. Il n’existe aucune transaction distribuée avec Listing, Geography ou Authoring.

## Replay

Le checksum SHA-256 canonique couvre la version du contrat, PropertyId, owner, version Authoring attendue, snapshot normalisé complet et `occurredAt` UTC. `commandId` est la clé du ledger mais n’entre pas dans son propre checksum.

- même `commandId` et même checksum : `AlreadyApplied` ;
- même `commandId` et checksum différent : `DivergentCommand` ;
- Aggregate existant compatible : `AlreadyApplied` ;
- Aggregate existant incompatible : `DivergentCommand`.

Aucune seconde Property n’est créée.
