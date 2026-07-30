# Phase 5.1B — Identity Claim Contract

## 1. Owner

Owner unique : `IdentityAccess / Identity Claims`.

IdentityClaimRegistry est l'autorité d'unicité des emails et téléphones
canoniques après cutover.

## 2. Types et états

Types fermés V1 : `Email`, `Phone`.

```text
Reserved → Active
Reserved → Released
Reserved → Expired
Active → Superseded
```

Une ancienne claim Superseded reste réservée contre la réattribution tant que
la policy de sécurité ne l'autorise pas. V1 retient la **réservation
permanente** des anciennes claims ; aucune réutilisation.

## 3. Commands

- `SeedHistoricalClaim`
- `ReserveClaim`
- `ActivateClaim`
- `ReleaseReservation`
- `ExpireReservation`
- `SupersedeActiveClaim`

Entrées : intent, AccountId, type, normalized fingerprint/value protégée,
normalizationVersion, timestamps, expected version.

## 4. Queries

- `ResolveActiveClaim(type, presented value)` retourne AccountId ou NotFound
  uniquement à un consumer interne ;
- `InspectClaimOwnership`
- `InspectReservation`.

## 5. Résultats fermés

`Seeded`, `Reserved`, `Activated`, `Released`, `Expired`, `Superseded`,
`AlreadyOwned`, `ClaimConflict`, `NotFound`, `VersionConflict`,
`ReplayConflict`, `NormalizationRejected`, `Indeterminate`.

## 6. Invariants

1. une valeur normalisée appartient à au plus un Account, tous états
   réservants confondus ;
2. toutes les claims 042 sont seedées avant mutation ;
3. réservation et activation utilisent le même Account/type/value/version ;
4. activation exige une ContactChange Verified ;
5. aucune libération d'une claim Active sans remplacement atomique ;
6. normalisation versionnée ;
7. résolution publique interdite ;
8. email et téléphone ne partagent pas un namespace ;
9. aucune mutation Snapshot V1 ;
10. Closed ne libère aucune claim.

## 7. Concurrence

Contrainte unique durable et transactionnelle ; un seul gagnant à deux
réservations concurrentes. Expected version et idempotency checksum
obligatoires. Aucun check-then-insert non atomique.

## 8. Confidentialité

La valeur peut être chiffrée pour usage autorisé et possède un fingerprint HMAC
pour unicité ; ni fingerprint ni valeur ne sortent en événement public.

## 9. Compatibilité

Les contraintes uniques 042 restent intactes. Leur autorité historique est
importée puis la Claim Registry devient canonique uniquement après gate 5.1D.
