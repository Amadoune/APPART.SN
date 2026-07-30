# Media Ownership Lookup

## Contrat

`MediaCollectionOwnershipLookup::resolve(propertyId)` retourne toujours un `MediaOwnershipResult` typé :

- `Found` avec l’unique `MediaCollectionId` persisté ;
- `Missing` sans identité de collection ;
- `Ambiguous` sans sélection implicite.

Le résultat conserve l’identité Property demandée afin que l’appelant puisse corréler la résolution sans état externe.

## Stratégie PostgreSQL

La requête utilise exclusivement l’égalité sur `media.media_collections.property_id`, ordonne les identités pour un comportement reproductible et applique `LIMIT 2`. L’index `media_collections_property_idx` garantit un accès ciblé.

La migration est idempotente avec `CREATE INDEX IF NOT EXISTS`. Son rollback opérationnel consiste à supprimer uniquement cet index ; aucune donnée ni relation d’ownership n’est créée ou transformée.

## Limites

Le lookup ne vérifie pas l’éligibilité des médias et ne charge pas l’Aggregate. Il ne décide pas quelle collection retenir en cas d’ambiguïté. La résolution Listing → Property reste fondée sur le `propertyId` déjà porté par Listing.
