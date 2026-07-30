# Phase 5.1B — Password Recovery Contract

## 1. Owner

Owner unique : `IdentityAccess / Password Recovery`.

Recovery prouve un challenge dédié puis délègue le changement de credential au
use case historique `ChangePassword`. Il ne réutilise jamais les tokens de
verification Snapshot V1.

## 2. États

```text
Issued → Consumed
Issued → Expired
Issued → Revoked
Consumed/Expired/Revoked → terminal
```

## 3. Commands et Query

| Command | Entrées |
|---|---|
| RequestRecovery | intent, login identifier secret, requestedAt, policyVersion |
| ConsumeRecovery | intent, challenge id, secret proof, new PasswordHash produit hors contrat, consumedAt |
| RevokeRecovery | intent, challenge id/AccountId, cause, occurredAt |
| ExpireRecovery | intent, challenge id, observedAt |

Query interne : `InspectRecoveryChallenge`.

## 4. Résultats

Public Request retourne toujours `Accepted`, y compris identité absente,
Closed, Suspended ou rate-limited.

Résultats internes fermés :

- `ChallengeIssued`
- `Suppressed`
- `PasswordChanged`
- `InvalidOrExpired`
- `AlreadyConsumed`
- `Revoked`
- `AccountUnavailable`
- `VersionConflict`
- `ReplayApplied`
- `ReplayConflict`
- `Indeterminate`

## 5. Invariants

1. challenge opaque, secret hashé, TTL borné et usage unique ;
2. un seul challenge actif par Account/channel selon policy ;
3. aucune présence d'Account révélée ;
4. la consommation exige Availability autorisée par policy Recovery ;
5. PasswordHash est produit par le hasher owner, jamais par le Domain à partir
   d'un secret clair ;
6. consommation, `ChangePassword` et invalidation globale des sessions sont
   atomiquement corrélées par orchestration ;
7. un rejeu identique ne change pas deux fois le password ;
8. aucune réouverture implicite d'un Account Closed/Suspended.

## 6. Concurrence et idempotence

Un seul consommateur gagne. Expected version du challenge et de l'Account
historique sont observées. Une divergence après changement de mot de passe
retourne Conflict sans réutiliser le challenge.

## 7. Confidentialité

Ni login, ni challenge secret, ni hash de password, ni cause interne de
suppression ne figurent dans event ou résultat public. Les notifications
reçoivent un message préconstruit ou une référence sécurisée, jamais le secret
dans un événement générique.

## 8. Événement

`PasswordRecoveryCompleted` peut être un événement privé minimal seulement si
un consumer Audit/Security est certifié : AccountId, recoveryId, occurredAt,
version. Aucun token ni canal.

## 9. Compatibilité

Utilise `ChangePassword` sans le modifier. `PasswordChanged` historique n'est
pas ajouté à Account Status Event V1/Outbox 043.
