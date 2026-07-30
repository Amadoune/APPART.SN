# ADR-1016 — Property Lifecycle Routing Destination

## Décision

Les événements Property Lifecycle sont transférés vers une Inbox PostgreSQL propriétaire de Real Estate Catalog avant toute compatibilité Outbox.

## Raisons

- le transfert durable est démontrable avant `Routed` ;
- l'Inbox conserve le contrat canonique sans transformation ;
- l'unicité de `event_id` rend le transfert idempotent ;
- la reprise reste découplée du futur Worker et des handlers ;
- aucune perte silencieuse n'est possible en l'absence de Consumer.

## Conséquences

Un Consumer Outbox futur pourra acquitter uniquement après `Routed`. L'Inbox devra être traitée par une capacité ultérieure explicitement certifiée ; 4.2G ne crée aucun handler implicite.
