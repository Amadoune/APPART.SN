# Projection DTO audit

V1 : `list<{label,url}>` dans `SeoListingProjection`.

V2 : `geographyBreadcrumb: list<{placeId,type,label}>`; le canonical Listing reste `canonicalUrl` séparé. parent/version restent source facts et n'ont pas besoin d'être dupliqués dans le read model public.

Aucun URL nullable ou placeholder.
