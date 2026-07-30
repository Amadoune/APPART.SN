# Reservation Lifecycle Outbox Owner Schema Resolution Specification

| Source module | Owner PostgreSQL |
|---|---|
| `ListingLifecycle` | `listing_lifecycle` |
| `RealEstateCatalog` | `real_estate_catalog` |
| `Media` | `media` |
| `SearchDiscovery` | `search_discovery` |
| `ContentSeo` | `content_seo` |
| `ReservationLifecycle` | `reservation_lifecycle` |

`PostgreSqlPublicProjectionOutboxSchema::for()` est la résolution normative du Writer. `moduleFor()` est sa bijection normative pour le Reader. Un schéma inconnu est refusé explicitement ; aucun fallback n'existe.

`all()` expose les six owners dans cet ordre. La résolution ne connaît ni catalogue événementiel, ni payload Reservation, ni Consumer : elle attribue uniquement la propriété physique des structures génériques.
