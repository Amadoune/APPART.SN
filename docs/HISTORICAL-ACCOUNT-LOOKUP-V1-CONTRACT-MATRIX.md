# Historical Account Lookup V1 — Contract Matrix

| Entrée / observation | Résultat | Autorité | Effet |
|---|---|---|---|
| identité valide, ligne historique intègre | `HistoricalAccountFound(accountId, isSuspended, historicalVersion)` | source Account historique | preuve de bootstrap uniquement |
| identité valide, absence attestée dans la source | `HistoricalAccountMissing` | source Account historique | futur `AccountMissing` selon la frontière 4.9C |
| ligne présente, identité divergente | `HistoricalAccountCorrupted` | lookup | rejet fermé |
| état non booléen ou non mappable | `HistoricalAccountCorrupted` | lookup | rejet fermé |
| version absente, négative ou non stable | `HistoricalAccountCorrupted` | lookup | rejet fermé |
| source absente ou non configurée | non classable en résultat métier | Runtime | graphe non résolvable / unhealthy |
| journal 041 sans ligne | aucune conclusion Account | store lifecycle | lifecycle non amorcé seulement |

## Invariants

1. `Found` contient exactement l'identité demandée.
2. `historicalVersion` est positive ou nulle selon la provenance certifiée et
   reste stable pendant la transaction d'amorçage.
3. `isSuspended=false` prépare `Active`; `true` prépare `Suspended`.
4. Le lookup n'écrit et ne décide jamais une transition.
5. `Missing` ne masque jamais corruption, indisponibilité ou défaut de
   configuration.
6. Le journal 041 ne produit jamais cette preuve lors de son propre amorçage.
