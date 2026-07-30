# Account Status Outbox — Owner Inventory

| Module owner | Schéma owner | Fondation |
|---|---|---|
| `ListingLifecycle` | `listing_lifecycle` | migration 005 |
| `RealEstateCatalog` | `real_estate_catalog` | migration 005 |
| `Media` | `media` | migrations 005/033 |
| `SearchDiscovery` | `search_discovery` | migration 005 |
| `ContentSeo` | `content_seo` | migration 005 |
| `ReservationLifecycle` | `reservation_lifecycle` | migration 021 |
| `ContactsLeads` | `contacts_leads` | migration 026 |
| `Professionals` | `professionals` | migration 030 |
| `AdministrationAudit` | `administration_audit` | migration 037 |
| `Geography` | `geography` | migration 040 |

Tous utilisent les contrats Writer, Reader, Mapper et Worker génériques
`PublicProjection*`.

Owner réservé pour Account Status :

```text
IdentityAccess ↔ identity_access
aggregate type : AccountStatus
event namespace : account.status.*
migration future : 043
```

Le schéma `identity_access` existe déjà pour les migrations 041/042, mais
aucune table `public_projection_outbox_*` n'y existe. La réservation est donc
additive et ne réutilise aucun owner Outbox historique.
