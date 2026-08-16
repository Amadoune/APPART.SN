# Audit de CertifiedPublicListingProjectionSource

| Source | Port | Owner | Store productif | Missing | Statut |
|---|---|---|---|---|---|
| Listing | `ListingRegistry` | ListingLifecycle | `listing_lifecycle.listings` | `ListingMissing` | obligatoire |
| Property | `PropertyRegistry` | RealEstateCatalog | `real_estate_catalog.properties` | `PropertyMissing` | obligatoire |
| ownership Media | `MediaCollectionOwnershipLookup` | Media | `media.media_collections` par `property_id` | `MediaOwnershipMissing/Ambiguous` | obligatoire |
| MediaCollection | `MediaCollectionRegistry` | Media | `media.media_collections` et items | `MediaCollectionMissing` | obligatoire |
| décision Search | `SearchDecisionReader` | SearchDiscovery | `search_discovery.public_search_decisions` | `SearchMissing/SearchCorrupted` | obligatoire pour version watermark |
| snapshot Content/SEO | `ContentSeoSourceSnapshotReader` | ContentSeo | `content_seo.public_source_snapshots` | `ContentSeoMissing/Corrupted` | obligatoire |
| génération active | `ActiveGenerationReader` | Public Projection | `public_projection.generations` | `ActiveGenerationMissing/Corrupted` | obligatoire |
| instant de décision | `DecisionTimeReader` | ContentSeo | snapshot Content/SEO | `DecisionTimeMissing/Corrupted/Divergent` | obligatoire |
| Geography publique | `PublicGeographyDecisionReader` | Geography publique | `public_geography.decisions` | absence tolérée à l'assemblage | nécessaire à readiness complète |
| Media public | `PublicMediaDecisionReader` | Media public | `public_media.decisions` | absence tolérée à l'assemblage | nécessaire à readiness complète |
| faits publics Authoring | `AuthoringPublicFactHandoffV1` | ListingLifecycle | `listing_lifecycle.authoring_public_fact_handoffs` | aucune erreur dédiée | optionnel, fournit `transactionKind` |

L'ordre d'arrêt est strict : Listing, Property, Media, Search, Content/SEO, génération, temps, Geography, Media public. Une source obligatoire absente interdit toute source partielle et aucun fallback n'existe.

Geography et Media public sont facultatifs pour produire un résultat d'assemblage `Found`, mais leur version nulle rend le watermark non prêt et bloque l'écriture de Projection.
