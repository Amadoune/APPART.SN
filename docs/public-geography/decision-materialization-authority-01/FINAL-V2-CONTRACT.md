# Final V2 contract

V2 est Geography-owned : schemaVersion, terminalPlaceId, status Available/Unavailable, locality, breadcrumb root→leaf et revisionVector. Chaque item porte placeId, type, officialName, parentPlaceId, aggregateVersion.

Aucun URL, slug, code, alias, ListingId ou PropertyId. Store JSONB compatible sans migration. Reader V2 doit distinguer Found/Unavailable/Missing/Corrupted.

Ce contrat est final côté source; son adaptation aval reste non autorisée.
