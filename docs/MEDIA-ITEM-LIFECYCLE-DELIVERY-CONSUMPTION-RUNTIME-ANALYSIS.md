# Media Item Lifecycle Delivery Consumption and Runtime Analysis

Le Sprint 4.6G-R1 ferme la politique qui traduira ultérieurement les résultats du routeur en résultats de consommation génériques. Cette décision est certifiée avant tout Consumer d'exécution.

Le Runtime compose uniquement le graphe déjà certifié :

```text
MediaItemLifecycleEventRouter
→ DurableMediaItemLifecycleEventRouter
→ MediaItemLifecycleInboxStore
→ PostgreSqlMediaItemLifecycleInboxRepository
→ PDO PostgreSQL Runtime existant
```

La politique de consommation est un singleton sans dépendance. Aucun appel à `route()`, `store()` ou `consumptionFor()` n'est effectué au bootstrap.
