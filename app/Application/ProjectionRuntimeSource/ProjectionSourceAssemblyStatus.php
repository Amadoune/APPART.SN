<?php

namespace App\Application\ProjectionRuntimeSource;

enum ProjectionSourceAssemblyStatus: string
{
    case Found = 'found';
    case InvalidListingIdentity = 'invalid_listing_identity';
    case ListingMissing = 'listing_missing';
    case PropertyMissing = 'property_missing';
    case MediaOwnershipMissing = 'media_ownership_missing';
    case MediaOwnershipAmbiguous = 'media_ownership_ambiguous';
    case MediaCollectionMissing = 'media_collection_missing';
    case SearchMissing = 'search_missing';
    case SearchCorrupted = 'search_corrupted';
    case ContentSeoMissing = 'content_seo_missing';
    case ContentSeoCorrupted = 'content_seo_corrupted';
    case PublicGeographyCorrupted = 'public_geography_corrupted';
    case PublicMediaCorrupted = 'public_media_corrupted';
    case ActiveGenerationMissing = 'active_generation_missing';
    case ActiveGenerationCorrupted = 'active_generation_corrupted';
    case DecisionTimeMissing = 'decision_time_missing';
    case DecisionTimeCorrupted = 'decision_time_corrupted';
    case DecisionTimeDivergent = 'decision_time_divergent';
    case SourceIdentityDivergent = 'source_identity_divergent';
}
