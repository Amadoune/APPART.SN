# APPART.TEST Public Search Results Read Model 01 — Field Exposure Matrix

| Champ candidat | Source publique | Décision V1 | Motif |
|---|---|---|---|
| `canonicalPath` | record de projection publique | EXPOSED | Identité de navigation certifiée |
| `listingId` | `PublicListingReadModel` | EXPOSED | Identifiant déjà public dans le read model |
| `headline` | `PublicListingReadModel` | EXPOSED, nullable | Titre public existant |
| `propertyType` | `PublicListingReadModel` | EXPOSED | Type public existant |
| `primaryImageUrl` | `PublicListingReadModel.publicMediaUrl` | EXPOSED, nullable | Média public déjà qualifié |
| prix | absent | NOT_SUPPORTED | Aucune valeur publique dans la projection |
| localisation lisible | absent | NOT_SUPPORTED | Seul un identifiant géographique interne est présent |
| transaction | absent | NOT_SUPPORTED | Non représentée par le read model |
| description | présente | NOT_EXPOSED | Non nécessaire à une carte minimale |
| surface/pièces | présentes | NOT_EXPOSED | Hors surface minimale retenue |
| revisions/checksum | infrastructure | FORBIDDEN | Données internes de validation |
