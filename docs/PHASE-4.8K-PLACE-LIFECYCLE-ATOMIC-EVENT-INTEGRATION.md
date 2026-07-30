# Phase 4.8K — Place Lifecycle Atomic Event Integration

## Chaîne atomique

```text
PlaceLifecycleAtomicEventOrchestrator
→ PostgreSqlAggregateOutboxTransaction
    → PlaceLifecycleOrchestrator
        → journal 038 + contexte V1
    → PlaceLifecycleEventCatalog
    → PlaceLifecycleDeliveryPayload
    → PostgreSqlPublicProjectionOutboxWriter
        → Outbox Geography 040
→ COMMIT unique
```

Le store Place détecte la transaction PDO active et y participe. Le Writer
générique utilise la même connexion. Aucun commit intermédiaire n'est permis.

## Règles

- seuls `Applied` et `AlreadyApplied` produisent l'événement et la Delivery;
- tout autre statut retourne sans appeler l'Outbox;
- un rejet Outbox provoque le rollback total;
- un rejeu exact confirme `AlreadyApplied` côté journal et côté Outbox;
- les verrous et contraintes certifiés de 038 et 040 restent les autorités de
  concurrence et d'idempotence.

## Composition

`PlaceLifecycleAtomicTransaction` est un alias de l'unique
`PostgreSqlAggregateOutboxTransaction`. L'orchestrateur atomique est un
singleton paresseux.

Les ports certifiés sont composés par :

- `PostgreSqlPlaceMergeContextInspector`, lecture exacte du journal 038;
- `DeterministicPlaceMergeReplayClassifier`, classification pure et fermée.

Ces implémentations sont additives : aucun contrat, aucune migration et aucune
responsabilité métier ne sont modifiés.

## Frontière

Aucun Writer, Reader, Mapper, Worker ou adapter spécialisé n'est créé. Les
migrations 038, 039 et 040 restent inchangées. Aucun HTTP n'est introduit.
Runtime Health demeure **Healthy — 55 capacités**.
