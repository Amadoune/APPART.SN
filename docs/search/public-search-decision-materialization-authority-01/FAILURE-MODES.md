# Failure Modes

| Mode | Réduction fail-closed attendue |
|---|---|
| Listing absent | `SourceMissing` |
| Listing non Published | `SourceMissing` ou résultat source non éligible à qualifier |
| Property absente | `SourceMissing` |
| Media absente/incohérente | `SourceMissing/SourceCorrupted` |
| révision source invalide | `SourceCorrupted` |
| checksum/source corrompu | `SourceCorrupted` |
| version de décision obsolète | `Obsolete` |
| même version, décision divergente | `Divergent` |
| writer indisponible | `DependencyUnavailable` |
| concurrence | résultat writer monotone ; jamais last-write-wins silencieux |
| rank non autoritatif | arrêt avant construction |
| facette non gouvernée | rejet Domain |

Les libellés Application définitifs restent à certifier après l'autorité Rank/facets. Aucun fallback Visible/rank zéro/facettes vides n'est autorisé implicitement.

## Completion 01

Les libellés et effets finaux sont certifiés dans `FINAL-FAILURE-MATRIX.md`. Rang `0` et facettes `[]` sont désormais la sortie explicite de policy v1 et ne constituent plus un fallback.
