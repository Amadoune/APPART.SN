# Phase 5.1B — Account Closure Contract

## 1. Owner

Owner unique : `IdentityAccess / Account Closure`.

Closure possède la volonté et l'état métier de fermeture. Il ne possède ni
Account Status, ni Sessions, ni les données cross-domain.

## 2. États

```text
Open → ClosureRequested → Closed
ClosureRequested → Open (Cancelled)
Closed → Reopened
```

Deleted, Anonymized et Erasure ne sont pas des états V1.

## 3. Commands

- `RequestAccountClosure`
- `ConfirmAccountClosure`
- `CancelAccountClosure`
- `ReopenAccount`

Entrées : intent, AccountId, actor, fresh-auth evidence, reason category
minimisée, occurredAt, expected version, policyVersion.

## 4. Queries

- `InspectClosure(AccountId)`
- `InspectClosureState(AccountId)` pour Availability.

## 5. Résultats fermés

`Requested`, `Closed`, `Cancelled`, `Reopened`, `AlreadyInState`,
`FreshAuthenticationRequired`, `ReopenNotAllowed`, `RetentionBlocked`,
`AccountMissing`, `VersionConflict`, `ReplayConflict`, `Indeterminate`.

## 6. Invariants

1. exactly one Closure authority par AccountId ;
2. Closed interdit l'accès mais ne modifie pas Account Status ;
3. Confirm exige Request valide et policy/cooling-off ;
4. Confirm requiert un session invalidation checkpoint atomiquement corrélé ;
5. Reopen ne réactive jamais Suspended ;
6. Reopen impossible après future ErasurePending/Completed, hors V1 ;
7. refs Listing/Lead/Reservation/Favorites/Audit conservées ;
8. aucune cascade delete ;
9. claims non libérées ;
10. Snapshot V1 inchangé.

## 7. Interactions

Closure Domain ne commande pas directement Sessions. L'orchestrateur 5.1F
persiste la décision Closure et `InvalidateAllSessions` dans une frontière
atomique propriétaire. Les consumers cross-domain observent Closure events et
décident localement.

## 8. Idempotence/concurrence

Intent/checksum et expected version obligatoires. Confirm/Reopen concurrents :
un seul gagne. Rejeu identique retourne l'état durable ; divergence est
ReplayConflict.

## 9. Confidentialité

La reason category est fermée et minimisée ; aucun texte libre/PII dans event.
Les diagnostics publics ne révèlent ni legal hold ni état interne détaillé.

## 10. Compatibilité

Catalogue/Store/HTTP/Outbox propriétaires futurs. Aucun nouvel état Status,
aucun changement 041–043, aucune anonymisation.
