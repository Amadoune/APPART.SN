# Readiness semantics

`PublicProjectionWatermark::readiness()` examine uniquement `publicGeographyVersion` et `publicMediaVersion`.

- deux valeurs nulles : `missing_public_geography_and_media_versions` ;
- Geography nulle : `missing_public_geography_version` ;
- Media nulle : `missing_public_media_version` ;
- deux entiers positifs : `ready`.

`CertifiedPublicProjectionCandidateFactory` réduit tout état non `ready` vers `CandidateBuildStatus::PromotionNotReady`; aucun candidate record n'est produit. Le producer du statut est donc le watermark assemblé, pas un store de génération.
