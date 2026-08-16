# Projection source audit

`CertifiedPublicListingProjectionSource` reçoit `PublicGeographyDecision` V1. L'URL n'intervient ni dans le lookup, ni dans readiness; elle est seulement convertie en DTO ContentSeo downstream.

V2 peut conserver locality, breadcrumb identifiés/typés et revision watermark. Source Assembly demeure Found sans URL dès que l'adapter V2 est utilisé.
