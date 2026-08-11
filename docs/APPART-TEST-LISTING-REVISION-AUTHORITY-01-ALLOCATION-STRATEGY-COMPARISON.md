# Listing Revision Authority 01 — Strategy Comparison

| Stratégie | Décision |
|---|---|
| réservation Registry persistée | rejetée : état supplémentaire inutile |
| allocator Application owner-scoped | retenue |
| dérivation depuis intent stable | retenue dans l'allocator |
| UUID fourni par HTTP | rejetée |
| mécanisme existant complet | absent |

La dérivation SHA-256 avec bits UUID v5 reprend une convention déjà utilisée dans le repository. Elle est pure, concurrent-safe, replayable et sans rollback à compenser.
