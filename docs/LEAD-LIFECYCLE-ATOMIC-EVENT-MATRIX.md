# Lead Lifecycle Atomic Event Matrix

| Transition certifiée | Événement | Version Outbox |
|---|---|---:|
| `Created → Deliver → Delivered` | `lead.lifecycle.delivered` | append version |
| `Created → Reject → Rejected` | `lead.lifecycle.rejected` | append version |
| `Delivered → Close → Closed` | `lead.lifecycle.closed` | append version |
| `Rejected → Close → Closed` | `lead.lifecycle.closed` | append version |

Chaque transition produit exactement un événement d'index `1`. L'identité métier SHA-256 et le `messageId` technique restent distincts et déterministes.
