# Matrice readiness et diagnostics

| Inspection Listing | Résolution |
|---|---|
| toutes sources et watermark Ready | `Resolved` |
| Geography stable absente | `MissingPublicGeographyRevision` |
| Media publique stable absente | `MissingPublicMediaRevision` |
| les deux absentes | `MissingPublicGeographyAndMediaRevisions` |
| watermark structurel incomplet | `WatermarkIncomplete` |
| source obligatoire absente | `SourceUnavailable` |
| checksum, identité, génération ou decisionAt divergent/corrompu | `Corrupted` |

Le Consumer mappe les absences de révisions vers `BlockedBySourceReadiness`, et les corruptions ou
identités invalides vers `PermanentFailure`.
