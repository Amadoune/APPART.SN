# Sprint 3.8B — analyse d’architecture

Le module SearchDiscovery possède un `SearchIndexRegistry` applicatif mais aucune implémentation de production. Implémenter maintenant un Repository Aggregate complet élargirait inutilement le périmètre. Le choix retenu est un store spécialisé des décisions Search finales, appartenant au module SearchDiscovery.

Le propriétaire Search fournit une `SearchDecision` après calcul. La décision contient son identité, le Listing concerné, une version positive et le `SearchProjection` final déjà décidé. L’adaptateur ne construit aucun ranking, facet, état ou révision source.

La persistance normalise explicitement le payload final en JSON : état, rank déjà décidé, facets ordonnées et trois révisions sources. Un checksum SHA-256 porte sur cette représentation canonique. Le mapper réhydrate les valeurs existantes uniquement pour vérifier leur validité et leur intégrité.

La lecture par Listing utilise la clé primaire et distingue `Found`, `Missing` et `Corrupted`. Une erreur de structure, une valeur Domain invalide, un état incohérent ou un checksum différent produit `Corrupted`, jamais un fallback.

L’écriture est monotone : une version supérieure remplace, une version identique converge vers `AlreadyApplied` ou `Divergent`, une version inférieure est rejetée. Le verrou et l’upsert conditionnel empêchent une concurrence de remplacer silencieusement une décision plus récente.
