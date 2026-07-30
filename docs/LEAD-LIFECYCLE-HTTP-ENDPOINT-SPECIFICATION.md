# Lead Lifecycle HTTP Endpoint Specification

## Requête

`POST /api/lead-lifecycles/{leadId}/transitions`

```json
{
  "action": "deliver",
  "expectedVersion": 1,
  "actorId": "aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa",
  "occurredAt": "2026-07-22T10:11:12.123456Z",
  "recordedAt": "2026-07-22T10:11:13.123456Z"
}
```

Actions admises : `deliver`, `reject`, `close`. Les instants utilisent obligatoirement UTC, six chiffres de microsecondes et le suffixe `Z`. `recordedAt` ne précède jamais `occurredAt`.

## Réponse

Le corps contient exclusivement `status` et `diagnostic`. Aucun détail PostgreSQL, Outbox, événement ou intégrité n'est exposé.
