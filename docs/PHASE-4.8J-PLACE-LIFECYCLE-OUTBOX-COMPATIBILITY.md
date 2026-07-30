# Phase 4.8J — Place Lifecycle Outbox Compatibility

## Composition livrée

La capacité Place Lifecycle réutilise exclusivement la chaîne générique :

```text
PlaceLifecycleDeliveryPayload
→ PublicProjectionDeliveryPayload
→ PostgreSqlPublicProjectionOutboxWriter
→ geography.public_projection_outbox_*
→ PostgreSqlPublicProjectionOutboxReader
→ PostgreSqlPublicProjectionOutboxMapper
→ PublicProjectionDeliveryWorker
→ PlaceLifecycleDeliveryConsumer
```

Aucun Writer, Reader, Mapper, Worker, Payload, Consumer ou adapter spécialisé
n'est ajouté.

## Application des amendements

Le payload et le Consumer existants implémentent leurs ports génériques. Le
Consumer valide et restaure le payload avant de reconstruire l'enveloppe 4.8G.

```text
transportChecksum() → Value Object 4.8G
checksum()          → transportChecksum()->value
```

La frontière Consumer applique exclusivement :

```text
Acknowledged → Consumed
Retry        → RetryableFailure
Quarantined → PermanentFailure
```

## Owner et migration

Le mapping bidirectionnel certifié est `Geography ↔ geography`. La migration
additive et réversible 040 crée uniquement :

- `geography.public_projection_outbox_messages`;
- `geography.public_projection_outbox_deliveries`;
- `geography.public_projection_outbox_cursors`;
- `geography.public_projection_outbox_replays`.

Son rollback supprime uniquement ces quatre tables. Les migrations 038 et 039
et leurs structures restent inchangées.

## Catalogue, mapper et Worker

Le catalogue générique accepte exactement `place.lifecycle.enabled`,
`place.lifecycle.disabled` et `place.lifecycle.merged`. Le mapper générique
restaure le payload existant. Le Worker ajoute une registration unique V1 par
type et réutilise le singleton Consumer existant.

## Runtime Health

La capacité `place_lifecycle_delivery_consumer`, réservée lors de 4.8I, couvre
la frontière utilisée par le Worker. Aucune capacité supplémentaire n'est
créée. Le catalogue demeure **Healthy — 55 capacités**.

## Frontière

4.8J n'introduit ni orchestration atomique, ni publication transactionnelle,
ni HTTP, ni logique métier.
