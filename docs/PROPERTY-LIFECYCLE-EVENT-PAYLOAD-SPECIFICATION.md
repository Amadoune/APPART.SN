# Property Lifecycle Event Payload Specification

Le payload V1 immuable contient, dans cet ordre normatif :

1. `propertyId` ;
2. `previousState` ;
3. `state` ;
4. `action` ;
5. `lifecycleVersion`.

Les quatre premières valeurs reprennent exactement l'identité et la transition certifiées. `lifecycleVersion` est la version persistée et doit être supérieure ou égale à 2.

Le payload ne contient aucune donnée HTTP, PostgreSQL, Outbox, Worker, Projection ou transport. Toute modification de forme ou de sémantique exige une nouvelle version.
