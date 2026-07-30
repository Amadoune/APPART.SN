# Historical Redirect HTTP Orchestration Specification

1. Lire la projection exacte via `PublicListingQuery`.
2. Si elle existe, rendre le ReadModel inchangé en 200 et arrêter.
3. Encapsuler l'identité publique demandée dans `CanonicalUrl`.
4. Appeler `HistoricalCanonicalQualifier`.
5. Pour Current ou Unknown, rendre 404; pour Ambiguous ou Corrupted, rendre 503.
6. Pour Historical uniquement, transmettre le `HistoricalCanonical` fourni au resolver.
7. Mapper les sept résultats du resolver sans branche `default`.

Le resolver est appelé au plus une fois. La destination n'est ni reparsée, ni normalisée, ni suivie. Seul `Resolved` produit une redirection.
