# Media Item Lifecycle HTTP Runtime Analysis

Le Sprint 4.6J expose une unique frontière HTTP :

```text
POST /api/media-item-lifecycles/{mediaId}/transitions
```

L'adaptateur valide le transport, construit mécaniquement `MediaItemLifecycleAtomicEventRequest`, délègue une seule fois à l'intégrateur 4.6I et traduit le résultat fermé.

Il ne dépend ni du workflow, ni des stores, ni de PostgreSQL, ni de l'Outbox. La décision concernant le média principal est reçue explicitement ; aucune lecture de `MediaCollection` n'est réalisée.
