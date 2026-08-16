# Test Matrix

Tests d’implémentation obligatoires :

- Unit : canonical RC2, UUIDv5 `8ae22fb5-5c87-547f-80db-1b41b9d401b4`, decisionAt Published, indexability déléguée, version/replay/obsolete/divergent et résultats de source ;
- Feature/composition : bindings, owner ContentSeo, handoff `ListingPublished` ;
- PostgreSQL : Applied, reader Found, DecisionTime Found, AlreadyApplied, évolution, obsolete/divergent, concurrence et catch-up Published réel ;
- Architecture : aucun SQL Application, aucune lecture Projection, aucune écriture Search, aucune horloge/random, transaction owner-locale, aucune migration ;
- intégration read-only : Published → snapshot → DecisionTime → assemblage sans `ContentSeoMissing`.
