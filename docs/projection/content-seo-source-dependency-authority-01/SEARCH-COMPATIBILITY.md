# Search Compatibility

La SearchDecision RC2 demeure Found, visible, version 1, rank 0, facets vides. ContentSeo doit seulement lire le résultat owner Search nécessaire à `SearchSeoSource` et sa révision.

Il ne modifie ni SearchDecision ni Search Runtime/UX/API. Search materialization et ContentSeo materialization peuvent converger indépendamment vers la readiness Projection. Aucun cycle SearchDiscovery ↔ ContentSeo n’est nécessaire.
