# Phase 5.2B — MediaProcessing Contract

## Commands

- `EnqueueProcessingV1`
- `ClaimProcessingV1`
- `CompleteProcessingV1`
- `FailProcessingAttemptV1`
- `QuarantineProcessingV1`

Résultats fermés : `Applied`, `AlreadyApplied`, `DivergentIntent`,
`NotClaimable`, `LeaseLost`, `RetryScheduled`, `TerminallyRejected`,
`TemporarilyUnavailable`.

## Query

`GetProcessingStatusV1` retourne `Pending`, `Leased`, `RetryScheduled`,
`Completed`, `Rejected` ou `Unavailable`.

## Invariants

- un lease appartient à un worker opaque et expire ;
- un résultat n’est accepté que pour le lease courant ;
- retry borné, backoff déterministe et quarantaine terminale ;
- recette, codec et version de normalisation font partie du checksum
  d’intention ;
- les variantes sont nommées par rôle fermé, jamais par chemin client ;
- une variante complète est reproductible depuis le canonique et la recette ;
- les diagnostics détaillés restent internes.

## Recettes MVP

Les rôles normatifs sont `thumbnail`, `card`, `gallery` et `original_safe`.
Leurs dimensions, qualité, format de sortie, métadonnées conservées et budget
CPU/mémoire devront être fixés dans le blueprint d’implémentation avant code.
