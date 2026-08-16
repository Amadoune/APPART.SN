# DTO URL alignment

## Audit

V1 impose `PublicGeographyBreadcrumbItem(label,url)`. Le mapper, `CertifiedPublicListingProjectionSource`, `ContentSeo\BreadcrumbItem`, la projection SEO, le read model et la Blade utilisent réellement l'URL.

## Décision

Option **C** : introduire une V2 sans URL. V1 reste historique et lisible; aucune URL vide/nullable/fictive n'est autorisée. V2 porte les identités/types/labels/parents/versions certifiés.

Les consumers V2 devront exposer un breadcrumb non navigable. Cette adaptation versionnée ne change ni canonical Listing ni route. Le JSONB accepte V2 sans migration SQL.
