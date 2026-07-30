# Place Lifecycle Outbox — Inventaire des owners existants

| Module owner | Schéma owner | Fondation |
|---|---|---|
| `ListingLifecycle` | `listing_lifecycle` | migration générique 005 |
| `RealEstateCatalog` | `real_estate_catalog` | migration générique 005 |
| `Media` | `media` | migration générique 005 |
| `SearchDiscovery` | `search_discovery` | migration générique 005 |
| `ContentSeo` | `content_seo` | migration générique 005 |
| `ReservationLifecycle` | `reservation_lifecycle` | migration 021 |
| `ContactsLeads` | `contacts_leads` | migration 026 |
| `Professionals` | `professionals` | migration 030 |
| `AdministrationAudit` | `administration_audit` | migration 037 |

Tous réutilisent les contrats, Writer, Reader, mapper et Worker génériques
`PublicProjection*`. Aucun owner ne possède un Worker spécialisé.

Les quatre tables conventionnelles sont :

- `public_projection_outbox_messages`;
- `public_projection_outbox_deliveries`;
- `public_projection_outbox_cursors`;
- `public_projection_outbox_replays`.
