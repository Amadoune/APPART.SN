# Runtime Binding Matrix

| Contrat | Implémentation de production |
|---|---|
| `PublicProjectionSourceLookup` | `CertifiedPublicProjectionSourceLookup` |
| `InspectablePublicListingProjectionSource` | `CertifiedPublicListingProjectionSource` |
| `PublicListingProjectionSource` | `CertifiedPublicListingProjectionSource` |
| `PublicGeographyDecisionReader` | `PostgreSqlPublicGeographyReader` |
| `PublicMediaDecisionReader` | `PostgreSqlPublicMediaReader` |
| `MediaCollectionPropertyResolver` | `RegistryMediaCollectionPropertyResolver` |
| `PropertyListingsResolver` | `PostgreSqlPropertyListingsResolver` |
| `MultiTargetPropagationStrategy` | `PagedMultiTargetPropagationStrategy` |
| `PublicProjectionUpdateExecutor` | `PublicListingProjectionUpdaterExecutor` |
| `PublicListingProjectionWriter` | `PostgreSqlPublicListingProjectionStore` |
| `PublicListingQuery` | `PostgreSqlPublicListingProjectionStore` |
| `PublicProjectionRebuildEnumerator` | `PostgreSqlPublicProjectionRebuildEnumerator` |
| `PublicProjectionCandidateFactory` | `CertifiedPublicProjectionCandidateFactory` |
| `PublicProjectionDeliveryConsumer` | `PublicProjectionUpdaterConsumer` |
| `RuntimeHealthInspector` | `DeterministicRuntimeHealthInspector` composé depuis le conteneur |

Les Repositories Listing, Property et Media et les readers Search/ContentSEO sont liés à leurs
adaptateurs PostgreSQL déjà certifiés, sans modification de ceux-ci.
