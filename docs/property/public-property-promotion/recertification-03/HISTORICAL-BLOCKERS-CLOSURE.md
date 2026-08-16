# Fermeture des blockers historiques F7

| Blocker historique | Cause | Autorité de fermeture | Preuve terminale |
|---|---|---|---|
| NO GO 01 | Échec fermé après Promotion sans rollback complet du Listing | F7-A | Aggregate, Workflow, public facts, outbox, delivery et PublicationReview reviennent à l’état antérieur; retry convergent |
| NO GO 02 | AddressId absent de la compatibilité ledgerless | F7-B | AddressId canonique → `AlreadyApplied`; AddressId divergent seul → `DivergentCommand` |

Les verdicts historiques ne sont pas réécrits. La présente campagne démontre uniquement que leurs causes sont fermées.
