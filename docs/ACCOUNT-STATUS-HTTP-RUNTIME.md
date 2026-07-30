# Account Status — HTTP Runtime

## Surface

```text
POST /api/account-statuses/{accountId}/suspend
POST /api/account-statuses/{accountId}/reactivate
```

Aucune action libre n'est reçue dans le payload. Les deux routes déterminent
exclusivement `Suspend` ou `Reactivate`.

## Sécurité

- authentification : Bearer token configuré par
  `ACCOUNT_STATUS_HTTP_BEARER_TOKEN`, minimum 32 caractères ;
- autorisation : scope exact
  `identity_access.account_status.manage` ;
- `actorId` reste une donnée d'audit du contexte et ne confère aucun droit ;
- validation stricte et rejet de tout champ inconnu ;
- réponses minimales sans secret ni diagnostic technique ;
- CSRF non applicable à ces deux routes sans session ni cookie ; l'exemption
  est locale et la protection Bearer reste obligatoire.

## Payload fermé

```text
currentState
contextVersion = 1
expectedVersion
observedVersion
actorId
occurredAt
recordedAt
intentId
```

Sont notamment interdits : action libre, `historical_version`, Credential,
token, rôle, Consent, Event payload, destination et routing proof.

## Matrice HTTP

| Résultat | HTTP | Code stable |
|---|---:|---|
| `Applied` | 200 | `account_status.applied` |
| `AlreadyInState` | 200 | `account_status.already_in_state` |
| `AccountMissing` | 404 | `account_status.not_found` |
| `VersionConflict` | 409 | `account_status.version_conflict` |
| `InvalidContext` | 422 | `account_status.invalid_context` |
| `PersistenceRejected` | 409 | `account_status.persistence_rejected` |
| `PersistenceCorrupted` | 503 | `account_status.persistence_unavailable` |
| `InspectionCorrupted` | 503 | `account_status.inspection_unavailable` |

Le Controller appelle exclusivement `AccountStatusAtomicEventOrchestrator`.
Il n'ouvre aucune transaction et n'accède ni au Workflow, ni au Router, ni à
l'Outbox, ni au Repository Historical Account.
