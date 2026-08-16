# Modèle d'ownership

## Autorités

- ListingLifecycle décide l'état Published et fournit la révision Listing.
- RealEstateCatalog possède les faits Property.
- Media possède collection, principal et révision Media.
- SearchDiscovery possède la décision finale de visibilité, rang, facettes et sa version.
- ContentSeo possède headline, description, canonical et indexabilité SEO.
- Public Projection possède le read model public matérialisé.

La décision attendue appartient bien à SearchDiscovery. Projection n'est pas autorisée à fabriquer rank, facettes ou version Search. Inversement, l'expérience Search publique actuelle ne possède pas le read model : elle lit `public_projection.listing_projections`.

## Conclusion

Il existe une dépendance réciproque au niveau des modules (`Projection → décision SearchDiscovery`, `Search public → Projection`), mais le flux certifié peut rester acyclique si la décision Search est produite depuis les faits publics owners **avant** Projection. Le chaînon absent est précisément ce producteur amont.
