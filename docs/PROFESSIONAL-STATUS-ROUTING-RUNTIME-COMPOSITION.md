# Professional Status Routing Runtime Composition

Le graphe Laravel certifiable est :

```text
ProfessionalStatusEventRouter
→ DurableProfessionalStatusEventRouter
→ ProfessionalStatusInboxStore
→ PostgreSqlProfessionalStatusInboxRepository
→ PDO PostgreSQL Runtime existant
```

Le serializer, le repository, le routeur et la politique sont des singletons. Les interfaces sont des alias partageant exactement les instances concrètes. Aucune lecture, transaction, route ou consommation n'est exécutée au bootstrap.
