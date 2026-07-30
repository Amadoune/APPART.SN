# Lead Lifecycle Routing Runtime Binding Specification

Le graphe de production est :

```text
LeadLifecycleEventRouter
→ DurableLeadLifecycleEventRouter
→ LeadLifecycleInboxStore
→ PostgreSqlLeadLifecycleInboxRepository
→ PDO PostgreSQL Runtime existant
```

`LeadLifecycleTransportSerializer` et `LeadLifecycleDeliveryConsumptionPolicy` sont également des singletons. Chaque port est un alias vers son unique implémentation. Tous les bindings sont paresseux.

Le bootstrap ne déclenche ni `route()`, ni `store()`, ni `consumptionFor()`, ni requête ou transaction PostgreSQL. Aucun Fake, Null Object ou fallback n'appartient au graphe.
