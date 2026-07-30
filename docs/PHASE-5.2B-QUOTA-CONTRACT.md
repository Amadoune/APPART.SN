# Phase 5.2B — MediaQuota Contract

## Commands

- `ReserveQuotaV1`
- `CommitQuotaV1`
- `ReleaseQuotaV1`
- `ExpireQuotaReservationV1`

Résultats : `Applied`, `AlreadyApplied`, `DivergentIntent`, `QuotaExceeded`,
`ReservationUnavailable`, `InvalidState`, `VersionConflict`.

## Query

`GetQuotaAvailabilityV1` retourne une vue bornée : `Available`, `Exhausted` ou
`Unavailable`, plus capacité restante arrondie si la politique publique
l’autorise.

## Invariants

- unité normative en octets reçus et nombre d’assets ;
- scope composé d’un actor opaque et d’une portée d’authoring opaque ;
- réservation atomique avant délivrance de la cible d’upload ;
- consommation monotone après finalisation acceptée ;
- libération/expiration exactement une fois par réservation ;
- jamais de compteur négatif ni de dépassement sous concurrence ;
- changement de politique versionné, sans réinterprétation rétroactive.
