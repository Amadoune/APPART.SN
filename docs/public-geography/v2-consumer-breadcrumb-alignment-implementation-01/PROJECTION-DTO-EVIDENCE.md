# Projection DTO evidence

`SeoListingProjection` expose additivement :

- `breadcrumbSchemaVersion`;
- `geographyBreadcrumb[{placeId,type,label}]`.

V1 reste `content-seo-breadcrumb-v1`. V2 est `public-geography-breadcrumb-v2`. Le builder effectue une copie déterministe et n’ajoute aucune URL au payload V2.
