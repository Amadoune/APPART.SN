# Phase 5.1B — Session Contract

## 1. Owner

Owner unique : `IdentityAccess / Sessions`.

Session possède les sessions interactives. Authentication demande leur
création ; Closure, Recovery/Password et Security peuvent demander leur
invalidation par un command public du owner.

## 2. Version et décision remember-me

Contrat `Session V1`.

`remember-me` est **exclu de V1**. Une durée longue, un token persistant ou un
refresh family dédié exigera une extension versionnée ultérieure.

## 3. États

```text
Active → Rotated
Active → Revoked
Active → Expired
Rotated/Revoked/Expired → terminal
```

Une rotation crée une nouvelle Session Active liée à la précédente ; elle ne
réactive jamais l'ancien secret.

## 4. Commands

| Command | Entrées principales |
|---|---|
| CreateSession | intent, AccountId, authentication evidence id, issuedAt, policyVersion, device key |
| RenewSession | intent, session id, presented secret proof, renewedAt, expected version |
| LogoutSession | intent, session id, occurredAt |
| InvalidateAllSessions | intent, AccountId, cause, occurredAt, checkpoint |
| ExpireSession | intent, session id, observedAt, expected version |

Causes fermées d'invalidation globale : `PasswordChanged`, `RecoveryCompleted`,
`AccountClosure`, `SecurityDecision`.

## 5. Queries

- `InspectSession(sessionId, secretProof, at)` ;
- `ListActiveSessionDescriptors(AccountId)` pour l'utilisateur, sans secret ;
- `CountActiveSessions(AccountId)`.

## 6. Résultats fermés

`Created`, `Renewed`, `Revoked`, `Expired`, `AlreadyApplied`, `NotFound`,
`InvalidProof`, `VersionConflict`, `PolicyRejected`, `AccountUnavailable`,
`ReplayConflict`, `Indeterminate`.

HTTP futur ne distingue pas NotFound d'InvalidProof.

## 7. Invariants

1. secret opaque à entropie suffisante, stocké uniquement sous forme de hash ;
2. session liée à un seul AccountId ;
3. expiration absolue et idle expiration obligatoires ;
4. renewal atomique et old secret invalidé ;
5. logout idempotent ;
6. invalidation globale possède un checkpoint monotone par Account ;
7. une session émise avant le dernier checkpoint est invalide ;
8. une session n'est valide que si Availability est Available à l'inspection ;
9. limite de concurrence déterminée par `policyVersion` ;
10. aucune session ne modifie Account Status ou Closure.

## 8. Concurrence

- expected version sur renew/expire/revoke ;
- un seul gagnant à deux renew concurrents ;
- Create respecte atomiquement la limite de sessions ;
- InvalidateAll gagne sur une création/rotation antérieure ou simultanée via
  checkpoint ;
- un rejeu n'émet pas un second secret.

## 9. Confidentialité

Le secret n'apparaît dans aucun résultat durable, event, log ou descriptor.
Les descriptors exposent seulement session id public, device label minimisé,
issued/lastSeen/expiresAt et current flag.

## 10. Compatibilité

Store, contrats et futurs endpoints propriétaires. Aucun ajout à migration
042, Event V1, Outbox 043, Runtime Health 58 ou routes Account Status.
