# MEDIA INGESTION TO READY ASSET 01 — IMPLEMENTATION EVIDENCE

## Composants

- `MediaAssetReadinessV1` : contrat Runtime owner-scoped.
- `MediaAssetReadinessRequest`, `Result` et `Status` : entrée et résultats fermés.
- `DeterministicMediaAssetReadiness` : validation et promotion déterministes.
- `MediaBinaryObjectStore::inspect()` : relecture technique du blob réel.
- `LaravelFilesystemMediaBinaryObjectStore` : recalcul SHA-256 et taille depuis le disque privé.
- `MediaAssetReadinessServiceProvider` : singleton et alias nominatif.
- `MediaIngestionRuntimeV1::readiness()` : exposition technique dans le Runtime Media existant.

## Invariants démontrés

| Invariant | Preuve |
|---|---|
| transition réelle | état durable `quarantined` version 1 vers `ready` version 2 |
| blob inchangé | contenu avant/après strictement identique |
| checksum inchangé | checksum du payload conservé et comparé au SHA-256 recalculé |
| owner inchangé | owner dérivé du payload persistant et comparé à l'objet inspecté |
| storage key inchangée | clé canonique comparée et payload recopié sans mutation |
| idempotence | replay `AlreadyApplied`, version durable maintenue à 2 |
| fail-closed | blob corrompu rejeté, asset maintenu `quarantined` |
| aucune collection | zéro intention d'attachement dans la preuve PostgreSQL |

Il n'existe aucun SQL dans l'orchestrateur. La persistance passe exclusivement par `MediaAssetStore`.
