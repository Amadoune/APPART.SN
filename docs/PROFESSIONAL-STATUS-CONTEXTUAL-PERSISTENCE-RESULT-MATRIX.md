# Professional Status Contextual Persistence Result Matrix

| Situation | Résultat | Écriture |
|---|---|---|
| version et état attendus, append valide | `Applied` | transition + contexte |
| transition, version et contexte identiques | `AlreadyApplied` | aucune |
| transition et version identiques, contexte différent | `ContextDivergence` | aucune |
| version discontinue | `VersionConflict` | aucune |
| état ou transition divergents | `StateConflict` | aucune |
| contrainte de transition refusée | `TransitionRejected` | aucune validation partielle |
| transition sans contexte ou donnée invalide | `Corrupted` | aucune |
