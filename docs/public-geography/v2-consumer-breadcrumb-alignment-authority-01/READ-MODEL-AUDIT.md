# Read model audit

Le read model PHP stocke actuellement le breadcrumb complet label/url dans le payload de projection JSON/read_model, pas dans des colonnes SQL dédiées. URL n'est pas nullable dans sa forme V1.

Un `PublicListingReadModelV2` peut porter geographyBreadcrumb identifié/typé sans migration. La city doit être dérivée par `type=city`, non par position.
