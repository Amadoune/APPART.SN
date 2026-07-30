# Sprint 3.9A — Property-to-Listings Resolution Foundation

## Décision d'architecture

La relation officielle est `PropertyId -> 0..N ListingId`. Elle est lue directement depuis
`listing_lifecycle.listings.property_id` par un read-side spécialisé. Aucun Repository Aggregate,
Consumer Delivery ou composant de projection n'est impliqué.

Le port applicatif sépare le protocole de pagination de l'adaptateur PostgreSQL. Le résultat fermé
sépare l'état du parcours de son diagnostic. L'adaptateur ne filtre ni le statut ni la publication
d'un Listing : il restitue exhaustivement toutes les identités persistées pour la Property.

## Bornage et reprise

La requête applique `property_id = :property`, puis `id > :after`, ordonne par `id` et lit au plus
`limit + 1` lignes. Le checkpoint opaque contient une version, l'empreinte SHA-256 de la Property et
le dernier Listing rendu. Un checkpoint d'une autre Property est refusé explicitement.

## Limites volontaires

3.9A ne réalise aucun fan-out et ne modifie aucun Consumer. Il n'évalue ni readiness, ni projection,
ni règle métier. PostgreSQL impose le type UUID aux deux identités persistées ; une identité durable
non mappable est donc normalement empêchée par le schéma et reste néanmoins diagnostiquée par
l'adaptateur en cas d'échec de lecture ou de mapping.
