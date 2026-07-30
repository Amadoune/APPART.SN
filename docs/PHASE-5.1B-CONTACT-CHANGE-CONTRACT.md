# Phase 5.1B — Pending Contact Change Contract

## 1. Owner

Owner unique : `IdentityAccess / Pending Contact Changes`.

Il coordonne la preuve du nouvel email/téléphone sans modifier Profile avant
activation.

## 2. États

```text
Pending → Verified → Activated
Pending → Expired
Pending → Cancelled
Verified → Expired
Verified → Cancelled
Activated/Expired/Cancelled → terminal
```

## 3. Commands

- `RequestContactChange`
- `VerifyContactChange`
- `ActivateContactChange`
- `CancelContactChange`
- `ExpireContactChange`

Request exige fresh-auth evidence, AccountId, type, nouvelle valeur protégée,
claim reservation evidence, requestedAt et policyVersion.

## 4. Query

`InspectContactChange(changeId, AccountId)` retourne état/version/descripteur
masqué, jamais le secret.

## 5. Résultats fermés

`Pending`, `Verified`, `Activated`, `Cancelled`, `Expired`, `AlreadyApplied`,
`ClaimConflict`, `InvalidProof`, `FreshAuthenticationRequired`,
`AccountUnavailable`, `VersionConflict`, `ReplayConflict`, `Indeterminate`.

## 6. Invariants

1. challenge secret hashé, TTL et usage unique ;
2. claim réservée avant Pending ;
3. un seul changement pending par Account/type selon V1 ;
4. vérification ne modifie pas Profile ;
5. activation atomique : Profile + claim swap + revision ;
6. ancienne claim conservée Superseded ;
7. notification ancien et nouveau canal sans exposer le secret ;
8. Closed annule/refuse toute activation ;
9. token Historical Verification non réutilisé ;
10. event seulement après Activated.

## 7. Anti-takeover

Fresh authentication bornée dans le temps, rate limit, notification de
l'ancien canal, possibilité d'annulation avant activation selon policy, et
SecurityDecision capable de bloquer. Les détails temporels portent une
policyVersion.

## 8. Idempotence/concurrence

Intent/checksum et expected version obligatoires. Verify et Activate ont un
seul gagnant. Expire/Cancel concurrents avec Activate retournent l'état durable
sans double effet.

## 9. Confidentialité et compatibilité

Token et PII absents des events/logs/diagnostics publics. Nouveau store et
catalogue propres ; aucune modification des verifications 042 ou Account
Status.
