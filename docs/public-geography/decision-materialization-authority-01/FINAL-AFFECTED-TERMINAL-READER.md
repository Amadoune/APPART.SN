# Final affected terminal reader

`AffectedPublicGeographyTerminalReaderV1::read(mutatedPlaceId, cursor?, limit)` lit uniquement les décisions V2 existantes dont revisionVector contient l'identité.

Ordre `place_id ASC`, cursor opaque du dernier ID, limit borné. Résultats Available, Empty, Corrupted, DependencyUnavailable. JSONB suffit fonctionnellement; aucun index/migration requis.
