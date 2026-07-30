# Phase 5.1B — Profile Revision Contract

## 1. Owner

Owner unique : `IdentityAccess / Profile Revisions`.

ProfileRevision est un journal append-only des mutations Profile appliquées.
Il n'est ni un Event Store générique ni AdministrationAudit.

## 2. Types

Types V1 fermés :

- `ProfileEnrolled`
- `DisplayNameChanged`
- `EmailChanged`
- `PhoneChanged`

## 3. Append command

`AppendProfileRevision` reçoit :

- revision id déterministe ;
- AccountId et Profile version résultante ;
- type ;
- actor id et occurredAt ;
- source intent/change id ;
- policy/normalization version ;
- previous/new protected value references ;
- checksum.

## 4. Queries

- `ListOwnProfileRevisions(AccountId, cursor, limit)`
- `InspectRevision(revisionId)` pour audit autorisé.

Résultats : `Appended`, `AlreadyAppended`, `AppendConflict`, `Found`,
`NotFound`, `NotAuthorized`, `Indeterminate`.

## 5. Invariants

1. append-only ;
2. revision id unique ;
3. une revision par version Profile résultante ;
4. ordre strictement croissant par Profile ;
5. checksum identique au rejeu ;
6. aucune valeur claire requise pour la liste utilisateur ;
7. valeurs historiques protégées et purgeables selon rétention sans supprimer
   la preuve structurelle ;
8. aucune reconstruction de Snapshot V1.

## 6. Idempotence et concurrence

Même revision id/checksum : AlreadyAppended. Checksum divergent :
AppendConflict. L'append est dans la transaction atomique de mutation Profile.

## 7. Confidentialité

La vue utilisateur affiche type/date/actor descriptor. Les anciennes PII
restent chiffrées ou référencées et ne sont jamais des payloads d'événement.
Les accès privilégiés sont auditables.

## 8. Compatibilité

Journal propriétaire distinct des Account Domain Events historiques et de
AdministrationAudit ; aucun changement Snapshot/Event V1/Outbox Status.
