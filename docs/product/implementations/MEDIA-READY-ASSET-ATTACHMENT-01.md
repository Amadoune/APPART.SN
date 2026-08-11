# MEDIA READY ASSET ATTACHMENT 01

## Périmètre

Cette implémentation matérialise exclusivement l'attachement owner-scoped d'un `MediaAsset` durable à l'état `ready` vers une `MediaCollection`. Elle n'ouvre aucune façade HTTP, UI, collection parallèle, nouvelle migration ou dépendance Search/Projection.

## Chaîne

1. `MediaAssetStore` relit l'asset identifié par `mediaId`.
2. L'état `ready` et le checksum durable sont obligatoires.
3. L'intent 059 est contrôlé pour le replay ou la divergence.
4. La collection existante est relue ; si elle est absente, elle est créée une seule fois pour une Property existante.
5. L'association immuable collection/Property est contrôlée.
6. `MediaCollection::add()` applique les invariants existants.
7. `MediaCollectionRegistry::saveWithMediaReservation()` réserve définitivement le `MediaId` avec optimistic locking.
8. L'intent est marqué `applied` dans la même transaction.

Les assets `quarantined`, absents ou dont le checksum diverge sont refusés comme `InvalidMedia`. Aucun second lifecycle ou règle métier n'est créé.

## Transactions et idempotence

`PostgreSqlMediaAttachmentTransaction` porte la transaction locale et utilise un savepoint lorsqu'une transaction externe existe. L'intent, la création éventuelle de collection, la réservation du média et la mutation sont donc atomiques. Un replay exact retourne `AlreadyApplied`; un même intent portant d'autres identités retourne `DivergentIntent`.

Les collections historiques restent lisibles. Aucun backfill ni changement de migration n'est requis.
