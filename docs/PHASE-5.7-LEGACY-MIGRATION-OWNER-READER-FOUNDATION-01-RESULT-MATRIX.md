# Result Matrix — Legacy Migration Owner Readers

| Reader | Résultats source → publics |
|---|---|
| Inventory | Available → Available ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |
| Wave | Ready → Ready ; Blocked → Blocked ; Completed → Completed ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |
| Reconciliation | Matched → Matched ; Divergent → Divergent ; Pending → Pending ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |
| Quarantine | Empty → Empty ; ContainsItems → ContainsItems ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |
| Cutover | Ready → Ready ; Blocked → Blocked ; Completed → Completed ; Missing → Missing ; Corrupted → Corrupted ; DependencyUnavailable → DependencyUnavailable |

Le catalogue fermé couvre exactement 27 états contextuels certifiés. Les valeurs homonymes partagées sont représentées une seule fois dans `LegacyMigrationOwnerReaderStatus`, soit 12 symboles lexicaux fermés. La Policy réalise les 27 réductions par conversion stricte `from`, sans fallback ni valeur par défaut.

Chaque résultat public contient exclusivement le statut V1 correspondant et `observedAt` UTC canonique.
