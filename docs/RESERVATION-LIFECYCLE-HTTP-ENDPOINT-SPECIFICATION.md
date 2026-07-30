# Reservation Lifecycle HTTP Endpoint Specification

```text
POST /api/reservation-lifecycles/{reservationId}/transitions
```

La route exige un UUID. Le corps JSON contient exclusivement `action`, `expectedVersion`, `occurredAt` et `recordedAt`. L'identité provient exclusivement du segment `reservationId`.

La réponse contient exactement :

```json
{"status":"applied","diagnostic":null}
```

Le statut et le diagnostic changent selon le résultat applicatif ; aucun détail PostgreSQL, Outbox ou exception n'est exposé.
