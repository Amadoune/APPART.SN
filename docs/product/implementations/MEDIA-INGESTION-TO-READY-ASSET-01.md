# MEDIA INGESTION TO READY ASSET 01

## Périmètre

Cette implémentation matérialise exclusivement la promotion technique d'un `MediaAsset` de `quarantined` vers `ready`. Elle ne crée ni façade HTTP, ni attachement, ni collection, ni dépendance Property, Listing, Search ou Projection.

## Chaîne certifiable

1. `MediaAssetStore` relit l'asset durable et son payload owner-scoped.
2. `MediaBinaryObjectStore` inspecte le blob privé à sa clé canonique.
3. Le checksum SHA-256 et le nombre d'octets sont recalculés sur le contenu réellement relu.
4. Owner, storage key, checksum et taille sont comparés au payload durable.
5. Une seule écriture optimistic-lock promeut l'état vers `ready`, sans modifier le payload.

Une absence ou divergence du blob produit un échec d'intégrité fail-closed. Une indisponibilité technique reste `DependencyUnavailable`.

## Idempotence

Un asset déjà `ready`, dont le blob demeure intègre et identique, retourne `AlreadyApplied` sans nouvelle révision. Une concurrence gagnante est relue après `VersionConflict` et converge également vers `AlreadyApplied` lorsque le même asset est déjà prêt.

## Compatibilité

Les assets historiques restent lisibles. Aucun backfill et aucune migration ne sont introduits. Les états autres que `quarantined` et `ready` sont refusés sans mutation.
