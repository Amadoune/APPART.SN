# Idempotency and Replay

- `commandId` est créé une fois par l'orchestration Submit et conservé lors de tout retry.
- Checksum canonique : SHA-256 d'une sérialisation versionnée et ordonnée de `contractVersion`, `propertyId`, `ownerAccountId`, `expectedAuthoringVersion`, snapshot Authoring complet normalisé et `occurredAt` UTC. Le `commandId` n'est pas inclus dans son propre checksum.
- `occurredAt` est stable au replay ; un nouvel instant avec le même commandId est divergent.
- Avant mutation, le ledger est interrogé sous verrou. Même identité/même checksum/succès donne `AlreadyApplied`; même identité/checksum différent donne `DivergentCommand`.
- Une version Authoring différente avant le premier succès donne `VersionConflict`; le caller doit créer une nouvelle commande après relecture.
- Un Aggregate existant est comparé au mapping canonique. Compatible : `AlreadyApplied`; divergent : `DivergentCommand`.
- Aucun replay ne réappelle `PropertyRegistry::add` après succès et aucun second Aggregate n'est créé.

La clé d'unicité Domain reste `propertyId`; l'identité de commande protège l'intention, pas seulement le transport.
