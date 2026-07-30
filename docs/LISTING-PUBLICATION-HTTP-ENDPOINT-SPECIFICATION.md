# Listing Publication HTTP Endpoint Specification

## Endpoint

`POST /api/listing-publications/{listingId}/transitions`

`listingId` est un UUID conforme au Value Object certifié.

## Corps JSON

```json
{
  "action": "submit",
  "expectedVersion": 7,
  "occurredAt": "2026-07-20T10:11:12.123456Z",
  "recordedAt": "2026-07-20T10:11:13.123456Z"
}
```

Les actions admises sont : `submit`, `begin_review`, `approve_and_publish`, `request_changes`, `reject`, `withdraw`, `review_material_change`, `suspend`, `expire`, `reinstate`, `review_renewal`, `renew_directly`, `approve_republication` et `archive`.

L'endpoint n'offre aucune action par défaut. Les quatre champs sont obligatoires. `expectedVersion` est un entier positif et `recordedAt` ne peut précéder `occurredAt`.
