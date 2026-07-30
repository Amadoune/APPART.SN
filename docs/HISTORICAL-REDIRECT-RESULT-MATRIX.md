# Historical Redirect Result Matrix

| État observé | Statut | Cible | Diagnostic | Décision autorisée |
|---|---|---:|---|---|
| Une destination publique unique, distincte de la source | `Resolved` | Oui | Aucun | Rediriger vers la cible fournie |
| Canonical historique inconnue | `NotFound` | Non | `UnknownHistoricalCanonical` | Ne pas rediriger |
| Décision connue sans destination publique | `DestinationMissing` | Non | `PublicDestinationMissing` | Ne pas rediriger |
| Destination égale à la source | `LoopDetected` | Non | `DestinationEqualsSource` | Ne pas rediriger |
| Destination elle-même historique | `ChainDetected` | Non | `DestinationIsHistorical` | Ne pas suivre la chaîne |
| Plusieurs destinations publiques | `Ambiguous` | Non | `MultiplePublicDestinations` | Ne choisir aucune cible |
| Décision illisible ou incohérente | `Corrupted` | Non | `StoredDecisionCorrupted` | Ne pas rediriger |

La matrice est exhaustive. Aucun statut implicite et aucune branche `default` ne sont admis.
