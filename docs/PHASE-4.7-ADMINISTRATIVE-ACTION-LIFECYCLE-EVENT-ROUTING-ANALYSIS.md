# Phase 4.7G — Administrative Action Lifecycle Event Routing Analysis

## Décision

Le routeur durable matérialise l'enveloppe 4.7F dans une Inbox propriétaire `administration_audit`. Il ne transforme ni l'enveloppe ni l'événement canonique.

## Chaîne

```text
AdministrativeActionLifecycleTransportEnvelope
→ DurableAdministrativeActionLifecycleEventRouter
→ AdministrativeActionLifecycleInboxStore
→ PostgreSqlAdministrativeActionLifecycleInboxRepository
→ administration_audit.administrative_action_lifecycle_event_inbox
```

## Résultats

* `Stored` et `AlreadyStored` deviennent `Routed` ;
* `Unavailable` devient `Deferred / RouteUnavailable` ;
* `RetryableFailure` devient `RetryableFailure / TransferFailed` ;
* `Rejected` devient `Rejected / CorruptedEvent`.

Seul `Routed` autorise l'acquittement.

## Frontière

Aucune Outbox, composition Runtime, consommation, publication ou logique métier n'appartient à ce sprint.
