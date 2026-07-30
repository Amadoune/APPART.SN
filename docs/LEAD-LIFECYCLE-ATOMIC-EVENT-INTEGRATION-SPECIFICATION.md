# Lead Lifecycle Atomic Event Integration Specification

## Séquence normative

1. ouvrir la transaction générique Aggregate + Outbox ;
2. exécuter `LeadLifecycleOrchestrator` avec acteur et instant explicites ;
3. pour `Applied` ou `AlreadyApplied`, inspecter l'append contextuel exact ;
4. produire l'événement 4.4E et le payload opaque 4.4F ;
5. écrire le message dans l'Outbox owner `ContactsLeads` ;
6. valider seulement si l'Outbox retourne `Applied` ou `AlreadyApplied`.

Tout échec de persistance, d'inspection, de construction contractuelle ou d'Outbox retourne `PersistenceCorrupted` après rollback intégral.

## Métadonnées

`actor`, `occurredAt` et `recordedAt` sont obligatoires et fournis par l'appelant. `recordedAt` ne peut précéder `occurredAt`. `occurredVersion` est la version exacte de l'append inspecté.
