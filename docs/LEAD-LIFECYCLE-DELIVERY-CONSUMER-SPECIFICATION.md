# Lead Lifecycle Delivery Consumer Specification

Le Consumer accepte exclusivement un message respectant :

* payload `LeadLifecycleDeliveryPayload` ;
* owner `ContactsLeads` ;
* aggregate `LeadLifecycle` ;
* type et version identiques à l'événement ;
* `aggregateId = leadId` ;
* `aggregateVersion = occurredVersion` ;
* `eventIndex = 1`.

Toute divergence retourne `DivergentPayload` sans appel au routeur. Sinon, le Consumer restaure l'enveloppe, appelle une seule fois `LeadLifecycleEventRouter`, puis applique la matrice 4.4G-R1.

Il ne contient aucune logique de workflow, aucune transaction et aucun accès direct à PostgreSQL ou à l'Outbox.
