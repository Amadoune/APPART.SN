# Temporal Read Matrix

| Condition | Résultat |
|---|---|
| dernière révision dont `effectiveAt` et `recordedAt` sont antérieurs ou égaux à `observedAt` | `Available` et RevisionState interne |
| aucune révision visible | `Missing` |
| checksum, forme ou catalogue invalide | `Corrupted` |
| panne PDO | `DependencyUnavailable` |

L'ordre est déterministe : `effective_at DESC`, `recorded_at DESC`, puis `revision DESC`. Les instants sont reconstruits en UTC canonique.
