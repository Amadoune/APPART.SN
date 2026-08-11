# F2 — Implementation Evidence

## Composants

- Contrats publics owner-scoped : `BeginPublicationReviewV1`, `ApprovePublicationV1`.
- Résultat et catalogue fermés : `PublicationReviewCommandResult`, `PublicationReviewCommandStatus`.
- Composition : `DeterministicPublicationReviewCommands`.
- Port de transaction de file : `PublicationReviewCommandStore`.
- Adaptation PostgreSQL : extension de `PostgreSqlPublicationReviewQueue`, sans évolution de schéma.
- Composition Laravel : aliases singleton/lazy dans `PublicationReviewQueueServiceProvider`.

## Preuves de délégation

Le test Unit vérifie que Begin transmet au Gateway le `listingId` et la version de soumission portés par le QueueItem. Approve transmet la version mécanique suivante. Aucun accès Property, Media, Projection, Search, Registry ou Infrastructure n'existe dans Application Review.

## Preuves de cohérence

- Claimant et version sont vérifiés avant la délégation.
- Begin conserve la claim et incrémente la version.
- Approve conserve l'historique, marque `completed` et incrémente la version.
- Un replay identique retourne `AlreadyApplied`.
- Une réutilisation divergente du `commandId` retourne `DivergentCommand`.
- Une version obsolète retourne `VersionConflict`.
- Le rollback externe restaure QueueItem et ledger.
