# Phase 4.8J — Rapport de suspension avant implémentation

## Statut

```text
4.8J Outbox Compatibility
→ SUSPENDU AVANT IMPLÉMENTATION

4.8J-R2 Generic Delivery Contract Compatibility Amendment
→ SEUL JALON AUTORISÉ
```

## Preuves

- `PublicProjectionDeliveryEventCatalog::entry()` exige une
  `class-string<PublicProjectionDeliveryPayload>`;
- `PostgreSqlPublicProjectionOutboxMapper` restaure un
  `PublicProjectionDeliveryPayload`;
- `PublicProjectionDeliveryConsumerRegistration` exige un
  `PublicProjectionDeliveryConsumer`;
- `PlaceLifecycleDeliveryPayload` ne satisfait pas le premier port;
- `PlaceLifecycleDeliveryConsumer` ne satisfait pas le second port et sa
  signature d'entrée diffère.

## Garanties de suspension

- aucune migration 040;
- aucune table Outbox Geography;
- aucun mapping `Geography ↔ geography`;
- aucune extension de catalogue ou mapper;
- aucune registration Worker;
- aucune modification des contrats 4.8F à 4.8I.

Le blocage est contractuel, reproductible et antérieur à toute écriture
d'implémentation 4.8J.
