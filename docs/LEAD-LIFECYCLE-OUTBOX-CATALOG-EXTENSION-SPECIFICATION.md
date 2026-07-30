# Lead Lifecycle Outbox Catalog Extension Specification

| Type | Owner | Aggregate | Version | Payload |
|---|---|---|---|---|
| `lead.lifecycle.delivered` | `ContactsLeads` | `LeadLifecycle` | 1 | `LeadLifecycleDeliveryPayload` |
| `lead.lifecycle.rejected` | `ContactsLeads` | `LeadLifecycle` | 1 | `LeadLifecycleDeliveryPayload` |
| `lead.lifecycle.closed` | `ContactsLeads` | `LeadLifecycle` | 1 | `LeadLifecycleDeliveryPayload` |

La liste provient exclusivement de `LeadLifecycleEventType::cases()`. Aucun catalogue Lead parallèle n'existe.
