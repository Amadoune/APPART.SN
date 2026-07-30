# Phase 5.2B — Persistence Concurrency Strategy

## Sérialisation

Chaque store prend deux advisory locks transactionnels ordonnés : owner/ID puis
owner/intentId. Un intent est unique à son owner et persiste au-delà des
versions suivantes.

## Résultats

- nouvel intent et version attendue : `Applied` ;
- même intent/checksum, même après mutations ultérieures : `AlreadyApplied` ;
- même intent/checksum différent : `DivergentIntent` ;
- expectedVersion périmée : `VersionConflict` sans intent partiel ;
- collision SQL : `IdentityConflict` ;
- autre rejet PostgreSQL : `Rejected`.

## Atomicité

State et intent sont écrits dans une transaction unique. Le store rejoint une
transaction englobante existante sans la valider ni l’annuler. Lorsqu’il
possède la transaction, toute erreur rollbacke state et intent.

## Attachment

Le journal 059 réserve l’intent avant la future orchestration locale Media et
enregistre la version appliquée. L’orchestration complète
`AttachReadyMediaAssetV1` reste hors de cette fondation de persistence.
