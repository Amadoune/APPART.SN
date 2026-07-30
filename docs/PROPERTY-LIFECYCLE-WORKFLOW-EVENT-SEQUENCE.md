# Property Lifecycle Workflow Event Sequence

1. Le client applicatif fournit Property, action, version attendue, `occurredAt` et `recordedAt`.
2. La transaction PostgreSQL certifiée démarre.
3. L'orchestrateur 4.2D lit, décide et persiste.
4. Seuls `Applied` et `AlreadyApplied` poursuivent la séquence.
5. `PropertyLifecycleEventCatalog` produit exactement l'événement de la transition certifiée.
6. `PropertyLifecycleDeliveryPayload` conserve son enveloppe canonique.
7. `PublicProjectionDeliveryCatalogMessageFactory` construit l'identité technique déterministe.
8. l'Outbox existante applique ou reconnaît le message.
9. la transaction commit ; toute défaillance antérieure déclenche un rollback complet.

Le Worker, le Consumer, le routeur, HTTP et les Projections ne participent pas à cette transaction.
