# Final consumer compatibility

Audit de l'Implementation V2 précédente :

- `PostgreSqlPublicGeographyMapper` discrimine V1/V2 et rejette les schémas inconnus;
- `CertifiedPublicListingProjectionSource` adapte V2 Available vers locality et `(placeId,type,label)`;
- `PublicGeographySeoSourceV2` est distinct du modèle V1 URL-bearing;
- ContentSeo conserve canonical, indexability, decisionAt et structured data;
- `SeoListingProjection` et `PublicListingReadModel` transportent un breadcrumb V2 séparé;
- Blade rend V2 avec des `span` non navigables et conserve les liens historiques V1;
- aucune URL fictive, dépendance Search ou BreadcrumbList JSON-LD n'est introduite.

Preuve terminale certifiée : 54 tests/213 assertions, PostgreSQL ciblé 5 tests/13 assertions, PHPStan/Pint/Vite/Git PASS. V1 reste lisible sans migration ni réécriture.
