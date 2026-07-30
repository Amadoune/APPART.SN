# Historical Redirect PostgreSQL Specification

`PostgreSqlHistoricalRedirectResolver` implémente directement le port 3.10A. Il exécute une seule requête en lecture, exacte, ordonnée et bornée. `HistoricalRedirectDecisionMapper` valide chaque champ et le checksum avant de construire exclusivement une fabrique certifiée de `HistoricalRedirectResolution`.

Le checksum est SHA-256 sur les champs, séparés par LF, dans cet ordre : identifiant de décision, source, destination ou chaîne vide, qualification ou chaîne vide, révision. Aucun temps courant n'entre dans la décision.

Ordre de classification : aucune ligne, validation d'intégrité, cardinalité ambiguë, destination absente, boucle, chaîne, destination Current résolue. Aucune branche implicite ou `default` n'est autorisée.

Les erreurs de contenu deviennent `Corrupted`. Les erreurs opérationnelles PostgreSQL restent des erreurs d'infrastructure et ne sont pas transformées en décision métier.
