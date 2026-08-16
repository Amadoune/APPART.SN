# Completeness Rule

## CompleteForPromotion

Décision vraie uniquement si, pour la version Authoring observée :

1. propertyId, owner, référence et type sont présents et valides ;
2. surface, rooms, bathrooms, construction year et Address satisfont mécaniquement `PropertyTypePolicy` ;
3. lorsqu'une Address est requise, AddressId, GeographicPlaceId et AddressLine sont tous présents ;
4. le GeographicPlaceId provient d'une sélection autoritative et est `Usable` ;
5. une autorité BusinessYear fournit la valeur applicable ;
6. aucune source n'est issue de Projection, Search, SEO ou d'un fallback local.

Dans tous les autres cas : `IncompleteForPromotion` sans mutation.

Cette règle ne remplace pas la validation Domain : elle évite seulement une commande manifestement incomplète. `RegisterProperty` conserve la décision finale. Les changements postérieurs du snapshot créent une nouvelle version et invalident toute décision de complétude antérieure non consommée.
