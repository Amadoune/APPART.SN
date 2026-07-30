# Phase 5.2B — MediaUpload Contract

## Commands

### ReserveUploadV1

Entrées : `intentId`, actor auto-scopé, portée Property/Listing, taille
attendue, type déclaré informatif et timestamp.

Résultats : `Applied`, `AlreadyApplied`, `DivergentIntent`,
`InvalidRequest`, `AccountUnavailable`, `ScopeUnavailable`, `QuotaExceeded`,
`TemporarilyUnavailable`.

### FinalizeUploadV1

Entrées : `intentId`, `uploadId`, preuve opaque de réception et timestamp.

Résultats : `AcceptedForValidation`, `AlreadyApplied`, `DivergentIntent`,
`Unavailable`, `Expired`, `SizeMismatch`, `TemporarilyUnavailable`.

### AbandonUploadV1

Résultats : `Applied`, `AlreadyApplied`, `DivergentIntent`, `Unavailable`.

## Query

`GetUploadStatusV1` retourne uniquement `Reserved`, `Receiving`, `Validating`,
`Completed`, `Rejected`, `Expired` ou `Abandoned`, avec échéance publique si
applicable. `Unavailable` homogénéise absent, non autorisé et hors scope.

## Invariants

- une réservation a une échéance et une taille maximale immuables ;
- une finalisation n’est acceptée qu’une fois ;
- les octets reçus, pas les headers client, déterminent taille et contenu ;
- un upload expiré ou abandonné ne peut redevenir actif ;
- aucune session d’upload n’est une URL publique durable ;
- la compensation de quota est idempotente.
