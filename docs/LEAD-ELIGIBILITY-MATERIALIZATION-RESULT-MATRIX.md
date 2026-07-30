# Lead Eligibility Materialization Result Matrix

| Résultat | Condition |
|---|---|
| `Created` | version continue insérée |
| `AlreadyMaterialized` | même version et checksum identique |
| `StaleVersion` | version inférieure à la version courante |
| `DivergentVersion` | même version, contenu divergent hors relation |
| `IncoherentRevision` | révisions différentes ou instant non UTC |
| `RelationDivergence` | même version, destinataire normatif divergent |
| `ContinuityConflict` | première version différente de 1, saut de version ou instant non croissant |

Les résultats sont fermés. Les erreurs techniques imprévisibles ne sont pas transformées en décisions métier.
