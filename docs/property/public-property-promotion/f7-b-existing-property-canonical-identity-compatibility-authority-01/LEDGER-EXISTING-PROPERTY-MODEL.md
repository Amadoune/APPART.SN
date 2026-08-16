# Modèle ledger / Property existante

Ordre actuel et normatif : snapshot → ledger → contrôles snapshot → arguments canoniques → Property lookup → compatibilité.

| Situation | Décision | Mutation |
|---|---|---|
| ledger même commandId/checksum réussi | `AlreadyApplied` | aucune |
| ledger même commandId divergent | `DivergentCommand` | aucune |
| ledger absent, Property absente | exécuter `RegisterProperty`, puis ledger `Applied` atomique | Property + ledger |
| ledger absent, Property canoniquement compatible | `AlreadyApplied` | aucune, pas de backfill implicite |
| ledger absent, Property incompatible | `DivergentCommand` | aucune |
| Property historique sans ledger | appliquer l’une des deux lignes précédentes | aucune si déjà existante |

La FK restrictive et la transaction F6 rendent le ledger de succès autoritatif sur l’application historique. Un replay ledger identique ne transforme pas l’état courant en nouvelle source canonique.
