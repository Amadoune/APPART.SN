# Final source reader

Le reader owner-side de l'Implementation résout :

`ListingId → Listing → PropertyId → Property → Address → terminalPlaceId → Place + parents`.

Il relit chaque Place via les autorités owner existantes et construit la chaîne root→leaf avec `placeId`, `officialName`, `type`, `parentPlaceId`, `enabled`, `mergedInto` et `aggregateVersion`. Il refuse : identité absente, parent absent, cycle, incohérence de parent/type, corruption, chaîne inactive/merged pour Available ou dépendance indisponible.

ListingId ne devient jamais l'identité de décision. Il sert uniquement à résoudre le terminal lors du chemin initial et du catch-up. Le terminal materializer de refresh reçoit directement terminalPlaceId. Aucun SQL ne réside dans Application.
