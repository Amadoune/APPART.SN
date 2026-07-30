# Media Item Lifecycle Outbox Compatibility Analysis

Le Sprint 4.6H relie les contrats Media Item Lifecycle certifiés à l'Outbox générique sans créer de nouvelle infrastructure.

Les deux événements 4.6E sont ajoutés au catalogue Delivery avec le tuple :

```text
Media / MediaItemLifecycle / V1
```

Le mapper PostgreSQL restaure `MediaItemLifecycleDeliveryPayload` depuis son unique champ `canonicalEvent`. Le Consumer valide la cohérence des métadonnées, reconstruit uniquement l'enveloppe technique 4.6F, appelle une fois le routeur 4.6G puis applique exclusivement la politique 4.6G-R1.
