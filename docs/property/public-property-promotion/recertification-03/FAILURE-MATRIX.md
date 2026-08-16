# Matrice terminale des défaillances

| Défaillance | Promotion | Property / ledger | Listing et handoffs | Retry |
|---|---|---|---|---|
| authoring absent ou mauvais owner | AuthoringMissing / OwnershipMismatch | aucun changement | non Submitted | nouvelle entrée valide requise |
| version authoring conflictuelle | VersionConflict | aucun changement | non Submitted | version attendue requise |
| source incomplète | IncompleteAuthoring | aucun changement | non Submitted | compléter Authoring |
| Geography non utilisable ou règle Domain | DomainRejected | aucun changement | non Submitted | corriger la source autoritative |
| dépendance/corruption technique | DependencyUnavailable | rollback Property et ledger | non Submitted | rejouable après restauration |
| checksum ledger divergent | DivergentCommand | inchangés | non Submitted | commande divergente refusée |
| Property ledgerless incompatible, dont AddressId | DivergentCommand | Property intacte, aucun ledger | non Submitted | divergence à résoudre hors replay |
| échec fermé Listing après Promotion | Promotion durable | Property/ledger conservés | rollback Aggregate, Workflow et handoffs | Promotion AlreadyApplied puis Submit convergent |

Les blockers historiques d’atomicité Listing et de canonicalité AddressId sont inclus et fermés.
