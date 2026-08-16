# Breadcrumb payload

Chaque item contient exactement :

- `placeId`;
- `type`;
- `publicLabel`;
- `parentPlaceId` nullable;
- `aggregateVersion` positive.

Il ne contient ni URL, slug, coordonnées, code, alias, ListingId ni état UI.
