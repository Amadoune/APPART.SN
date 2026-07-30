# Professional Status Contextual Store Specification

Le port expose uniquement `read()` et `append(ProfessionalStatusContextualAppend)`.

Résultats fermés : `Applied`, `AlreadyApplied`, `ContextDivergence`, `VersionConflict`, `StateConflict`, `TransitionRejected`, `Corrupted`.

Un append valide écrit la transition versionnée et son contexte dans la même transaction. Un rejeu identique n'écrit rien. Une divergence de l'acteur ou de l'instant retourne `ContextDivergence`. Une transition identique sans contexte associé est `Corrupted`.
