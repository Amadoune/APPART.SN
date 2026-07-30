# Property Lifecycle Outbox Mapper Compatibility Specification

`PostgreSqlPublicProjectionOutboxMapper` demeure l'unique mapper Outbox. Pour un type reconnu par `PropertyLifecycleEventType`, il appelle exclusivement `PropertyLifecycleDeliveryPayload::restore()`.

Le round-trip conserve exactement `canonicalEvent`, son checksum SHA-256, l'`eventId` métier, le `message_id` technique, les métadonnées, le type, la version et l'ordre. Toute incohérence de checksum, d'idempotency key, de schéma ou de sérialisation canonique est rejetée. Les branches historiques et Listing Publication restent inchangées.
