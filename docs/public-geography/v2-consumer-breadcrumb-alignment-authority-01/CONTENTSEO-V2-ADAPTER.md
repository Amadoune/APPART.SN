# ContentSeo V2 adapter

ContentSeo reçoit un `PublicGeographySeoSourceV2` minimal : ListingId pour cohérence, locality et breadcrumb Geography `(placeId,type,label)`.

La policy conserve locality pour indexability/structured data. Elle transmet le breadcrumb informatif séparément du canonical Listing; aucun `BreadcrumbItem` V1 n'est créé.
