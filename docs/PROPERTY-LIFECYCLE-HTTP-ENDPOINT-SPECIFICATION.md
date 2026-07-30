# Property Lifecycle HTTP Endpoint Specification

`POST /api/property-lifecycles/{propertyId}/transitions`

Le paramètre `propertyId` doit être un UUID accepté par la contrainte de route Laravel. Le JSON requiert `action`, `expectedVersion`, `occurredAt` et `recordedAt`.

Actions acceptées : `activate`, `archive`, `begin_maintenance`, `mark_unavailable`, `decommission`, `complete_maintenance`, `restore_availability`. La valeur interne `unknown` et toute autre chaîne sont refusées.

`expectedVersion` est un entier positif. Les instants suivent exactement `YYYY-MM-DDTHH:MM:SS.ffffffZ` et `recordedAt` doit être postérieur ou égal à `occurredAt`.
