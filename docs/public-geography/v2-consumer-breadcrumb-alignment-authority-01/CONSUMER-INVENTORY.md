# Consumer inventory

| Consumer | Usage actuel URL |
|---|---|
| PostgreSqlPublicGeographyMapper | reconstruit V1 label/url |
| CertifiedPublicListingProjectionSource | transforme URL en ContentSeo CanonicalUrl |
| ContentSeo BreadcrumbItem/Policy | transporte URL; ajoute canonical Listing |
| SeoListingProjectionBuilder/DTO | sérialise label/url |
| PublicListingReadModel/Builder | stocke label/url; déduit city par position |
| public-listing Blade | rend `<a href>` |
| tests/commande P02 | fixtures V1 avec URL |

Search n'est pas consumer Public Geography direct.
