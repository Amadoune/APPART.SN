# Writer Audit

Le port `PublicProjectionGenerationManager` expose `createCandidate`, `activate` et `rollback`. L'adapter PostgreSQL rend `Applied`, `AlreadyApplied`, `GenerationNotFound`, `InvalidState` ou `ValidationFailed`. L'activation relit et verrouille la cible et l'Active, valide le manifeste, puis bascule dans une transaction locale.
