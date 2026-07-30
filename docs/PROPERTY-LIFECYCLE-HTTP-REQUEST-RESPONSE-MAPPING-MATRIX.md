# Property Lifecycle HTTP Request / Response Mapping Matrix

| Transport | Type applicatif | Transformation |
|---|---|---|
| route `propertyId` | `PropertyId` | validation UUID, aucune génération |
| `action` | `PropertyLifecycleAction` | conversion enum après whitelist |
| `expectedVersion` | entier positif | aucune |
| `occurredAt` | `PropertyLifecycleEventInstant` | validation canonique, aucune normalisation |
| `recordedAt` | `PropertyLifecycleEventInstant` | validation canonique et ordre, aucune normalisation |

Réponse stable : `{"status":"<statut>","diagnostic":null|string}`. Le diagnostic provient exactement du diagnostic workflow ou orchestration, sans traduction technique.
