# Projection Boundary

Invariant maintenu :

`RealEstateCatalog\Property → PropertyRegistry → CertifiedPublicListingProjectionSource → Public Projection → Search → Public Listing`.

Public Projection reste strictement read-only vis-à-vis de Property. Elle ne doit jamais :

- lire `PropertyAuthoringStore` ;
- utiliser `PropertyAuthoringCatalogAdapter` comme source publique ;
- transformer city/neighborhood en adresse ;
- créer ou compléter l'Aggregate ;
- inventer une référence, surface, place ou autre invariant.

Search, SEO et Public Listing consomment uniquement la projection. Les rebuilds répètent la lecture du Registry ; ils ne rejouent pas une promotion et continuent à échouer fermé si la source autoritative manque.
