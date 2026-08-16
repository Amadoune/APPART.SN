# Materialization Order

Après Published, SearchDecision et ContentSeo snapshot sont deux matérialisations owner-locales. ContentSeo a besoin de l’état/révision Search, donc son exécution réussie requiert Search Found ; le delivery peut toutefois être rejoué indépendamment sans transaction distribuée.

Ordre effectif candidat : Published → Search materialization → ContentSeo materialization → Projection readiness. Si ContentSeo est appelé avant Search, il retourne source missing et attend un replay.
