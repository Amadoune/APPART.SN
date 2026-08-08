# Legacy Migration Owner Reader Boundary Audit

## Décision

Le Boundary Audit retient `LegacyMigration` comme owner unique de coordination et `LegacyMigrationOwnerSource` comme source candidate unique. Les owners cibles conservent seuls toute autorité métier.

## Chaîne cible

```text
LegacyMigrationOwnerSource
        ↓
LegacyMigrationInventoryOwnerReader
LegacyMigrationWaveOwnerReader
LegacyMigrationReconciliationOwnerReader
LegacyMigrationQuarantineOwnerReader
LegacyMigrationCutoverOwnerReader
        ↓
LegacyMigrationInventoryReaderV1
LegacyMigrationWaveReaderV1
LegacyMigrationReconciliationReaderV1
LegacyMigrationQuarantineReaderV1
LegacyMigrationCutoverReaderV1
```

Les cinq Owner Readers sont futurs et ne sont pas créés par cet audit.

## Réductions qualifiées

| Frontière | Réduction homonyme |
|---|---|
| Inventory | Available → Available ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |
| Wave | Ready → Ready ; Blocked → Blocked ; Completed → Completed ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |
| Reconciliation | Matched → Matched ; Divergent → Divergent ; Pending → Pending ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |
| Quarantine | Empty → Empty ; ContainsItems → ContainsItems ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |
| Cutover | Ready → Ready ; Blocked → Blocked ; Completed → Completed ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |

Les réductions sont exhaustives, mécaniques, bijectives et homonymes. Aucun fallback, aucune agrégation, aucune décision métier, aucun Revision State, aucune donnée Legacy et aucune PII ne franchissent la frontière.

## Conclusion

La frontière est qualifiée sans code, implémentation, Provider, binding, test ou ouverture d'une Foundation ultérieure.
