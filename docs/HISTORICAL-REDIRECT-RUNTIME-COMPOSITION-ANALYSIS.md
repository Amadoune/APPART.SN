# Historical Redirect Runtime Composition Analysis

## Décision

La capacité 3.10B est composée dans l'unique racine Laravel existante, `PublicProjectionRuntimeServiceProvider`. Aucun provider spécialisé n'est créé. Le mapper et l'adaptateur sont des singletons paresseux ; le contrat est un alias vers l'unique adaptateur.

## Graphe

`HistoricalRedirectResolver` désigne `PostgreSqlHistoricalRedirectResolver`, construit avec le `PDO` PostgreSQL certifié du Runtime et l'unique `HistoricalRedirectDecisionMapper`.

L'enregistrement ne construit aucun objet, n'ouvre aucune connexion et n'exécute aucune requête. Une résolution explicite par le conteneur construit le graphe, mais seule l'invocation ultérieure de `resolve` peut lire PostgreSQL.

## Runtime Health

La capacité `HistoricalRedirectResolver` devient une exigence certifiée. L'inspection vérifie seulement que le contrat est enregistré et que l'objet construit implémente l'interface. Elle ne crée aucune `HistoricalCanonical` et n'appelle jamais `resolve`.

## Hors périmètre

HTTP, route, contrôleur, middleware, redirection, suivi de chaîne, décision SEO, canonical et toute modification des contrats 3.10A ou du stockage 3.10B.
