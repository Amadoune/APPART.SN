# Projection Property Dependency

## Contrat concret

`CertifiedPublicListingProjectionSource` reçoit explicitement :

`Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry`.

Après lecture du Listing, elle exécute :

`PropertyRegistry::find(RealEstateCatalog\PropertyId::fromString($listing->propertyId()->value))`.

Le type exigé est donc `Appart\Modules\RealEstateCatalog\Domain\Model\Property`, et non `PropertyAuthoringState`, un catalogue d'éligibilité ou une source Search/SEO.

## Condition exacte de PropertyMissing

`ProjectionSourceAssemblyStatus::PropertyMissing` est produit lorsque :

1. l'identité Listing est valide ;
2. `ListingRegistry::find` retourne un Listing ;
3. `PropertyRegistry::find` retourne `null` pour son `propertyId`.

L'assemblage s'arrête avant Media, Search, ContentSeo, génération active et decision time.

## Faits Property nécessaires à la projection finale

L'Aggregate alimente directement :

- `id`, `type`, `surface`, `rooms`, `address/placeId`, `version` ;
- le `SearchListingProjectionBuilder` ;
- le watermark Property ;
- le `PublicListingReadModel` (`propertyId`, `propertyType`, surface, rooms, geographic place/city selon les sources publiques).

Les décisions SEO apportent par ailleurs `PropertySeoSource.propertyType` et `city`, mais elles ne remplacent pas l'Aggregate : le constructeur `PublicListingProjectionSources` exige les deux.

## Statuts fermés de l'assemblage

`Found`, `InvalidListingIdentity`, `ListingMissing`, `PropertyMissing`, `MediaOwnershipMissing`, `MediaOwnershipAmbiguous`, `MediaCollectionMissing`, `SearchMissing`, `SearchCorrupted`, `ContentSeoMissing`, `ContentSeoCorrupted`, `PublicGeographyCorrupted`, `PublicMediaCorrupted`, `ActiveGenerationMissing`, `ActiveGenerationCorrupted`, `DecisionTimeMissing`, `DecisionTimeCorrupted`, `DecisionTimeDivergent`, `SourceIdentityDivergent`.

## Preuve RC2

Pour `add18bba-6635-4bda-aba9-e66d7bf4084e` : Listing Published et Queue completed existent, mais aucune ligne `real_estate_catalog.properties` correspondante. L'inspection retourne `property_missing`; aucun ledger Projection et aucune ligne de Projection Store ne sont créés.

## Interdiction de synthèse

Public Projection est un consumer read-only. Elle ne possède ni les invariants de `RegisterProperty`, ni l'autorité de référence, ni la décision permettant de convertir ville/quartier en `Address`/place géographique. Elle ne peut donc jamais synthétiser le Property manquant.
