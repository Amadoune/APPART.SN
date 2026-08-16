# Failure Modes

| Cas | Résultat owner | Mutation | Retry |
|---|---|---|---|
| Intention/UUID invalide | Rejet de Value Object/Authoring | Non | Après correction |
| Version Authoring divergente | Promotion `VersionConflict` | Non | Nouvelle lecture/commande |
| Replay identique | `Issued`, même ID | Non dans l'Issuer | Oui |
| Replay avec intention différente | Nouvel ID seulement si nouvel addressIntentId autorisé | Non dans l'Issuer | Selon promotion |
| Collision réelle | `Collision` | Non | Pas de fallback automatique |
| Storage indisponible | Promotion `DependencyUnavailable` | Rollback Domain | Même intention |
| Property déjà promu compatible | Promotion `AlreadyApplied` | Non | Autorisé |
| Address déjà matérialisée divergente | Promotion divergence/collision | Non | Intervention explicite |

L'Issuer pur n'a aucun mode ledger unavailable.
