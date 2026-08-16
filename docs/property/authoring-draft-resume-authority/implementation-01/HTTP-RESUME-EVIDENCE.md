# HTTP Resume Evidence

Surface : `GET /authoring/workspace/{listingId}`.

- `listingId` est l'unique identité client ;
- `AccountId` provient uniquement de l'attribut posé par le middleware IAM ;
- UUID invalide : HTTP 422 ;
- absent ou interdit : HTTP 404 non énumérable ;
- incomplet ou conflit d'état : HTTP 409 ;
- corrompu : HTTP 500 ;
- dépendance indisponible : HTTP 503 ;
- disponible : HTTP 200 avec bootstrap read-only.

La route sans `listingId` demeure le nouveau parcours normal. Les tests Feature couvrent session, owner, autre owner, UUID invalide et tous les mappings fermés.
