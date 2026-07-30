# Lead Lifecycle HTTP Runtime Analysis

Le Sprint 4.4J expose exclusivement l'intégrateur atomique 4.4I. HTTP valide et traduit le transport ; il ne lit ni workflow, store, inspecteur, catalogue d'éligibilité, PostgreSQL ou Outbox.

L'endpoint unique est `POST /api/lead-lifecycles/{leadId}/transitions`. La route impose un UUID et le body impose action, version attendue, acteur, `occurredAt` et `recordedAt` explicites.

L'adaptateur ne crée aucune transaction, aucune identité, aucun instant et ne publie aucun événement directement.
