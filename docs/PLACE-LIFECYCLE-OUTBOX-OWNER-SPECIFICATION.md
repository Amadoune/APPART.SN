# Place Lifecycle Outbox Owner — Spécification normative

## Décision

```text
OutboxOwner              Geography
ModuleOwner              Geography
SchemaOwner              geography
CatalogNamespace         PlaceLifecycle / place.lifecycle.*
WorkerNamespace          PublicProjectionDeliveryWorker::Geography.PlaceLifecycle
RuntimeHealthCapability  place_lifecycle_outbox_owner
```

`Geography` est l'unique owner des faits Place Lifecycle. `PlaceLifecycle`
reste le type d'agrégat de livraison, jamais un schéma ou un module concurrent.

## Structures futures réservées

Le futur sprint 4.8J pourra proposer, exclusivement dans `geography` :

```text
geography.public_projection_outbox_messages
geography.public_projection_outbox_deliveries
geography.public_projection_outbox_cursors
geography.public_projection_outbox_replays
```

La migration future réservée est **040**. Elle ne peut être créée avant le GO
formel de 4.8J-R1.

## Contrats futurs autorisables

- extension `Geography ↔ geography` du résolveur générique;
- extension du catalogue Delivery pour les trois types 4.8F;
- restauration du `PlaceLifecycleDeliveryPayload`;
- compatibilité du Writer, Reader et mapper génériques;
- enregistrement du consommateur logique 4.8I dans le Worker générique;
- capacité Runtime Health réservée `place_lifecycle_outbox_owner`.

Aucun Writer, Reader, Mapper, Consumer ou Worker spécialisé n'est autorisé.
