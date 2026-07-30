# Property Lifecycle Event Routing Analysis

Le routage établit la première destination de production du contrat 4.2F. `DurablePropertyLifecycleEventRouter` transmet l'événement inchangé à `PropertyLifecycleEventDestination` et traduit mécaniquement son résultat fermé.

`PostgreSqlPropertyLifecycleEventInbox` est l'unique destination. Elle sérialise avec le serializer 4.2E, calcule le checksum, puis garantit une insertion durable ou vérifie l'identité complète d'une ligne existante.

`Routed` signifie exclusivement `Stored` ou `AlreadyStored`. Une divergence ne peut donc jamais être acquittée. Aucune Outbox, publication ou consommation n'appartient à ce sprint.
