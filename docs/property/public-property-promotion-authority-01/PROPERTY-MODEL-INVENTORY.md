# Property Model Inventory

| Modèle | Namespace / persistance | Owner | Rôle et source de vérité | Création / mutation | Consommateurs observés |
|---|---|---|---|---|---|
| Property Authoring | `Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState`; table `real_estate_catalog_authoring.property_authoring` | RealEstateCatalog Authoring, owner-scoped par `ownerAccountId` | Intention de préparation : `propertyId`, owner, version, identité/checksum d'intent, `propertyType`, `city`, `neighborhood` | `DeterministicPropertyListingAuthoringOperations::property()` via `PropertyAuthoringStore::save`; optimistic version, advisory lock, replay par intent | Workspace/HTTP Authoring, création Listing via contrôle d'existence/owner, adapters Property pour Listing et Media |
| Aggregate Property | `Appart\Modules\RealEstateCatalog\Domain\Model\Property`; tables `real_estate_catalog.properties`, `property_addresses`, réservation de référence | RealEstateCatalog Domain | Source autoritative d'un Property enregistré : référence, type, surface, pièces, salles de bain, année, adresse, statut, version | `RegisterProperty`, `UpdateProperty`, `ChangeAddress`, `ArchiveProperty`; `PropertyRegistry` | Listing PropertyCatalog historique, Media Registry catalog, `CertifiedPublicListingProjectionSource`, lifecycle Property |
| Registry Property | Contrat `Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry`; implémentation `PostgreSqlPropertyRepository` | RealEstateCatalog Infrastructure au service du Domain | Port d'accès à l'Aggregate, pas un modèle supplémentaire | `find`, `add`, `save` avec identité/référence uniques et optimistic locking | Use cases Property et lecteurs inter-modules explicitement bindés |
| Property local P02 | Aggregate créé par `CreateLocalFirstListing` avec `Property::register` puis `PropertyRegistry::add` | Bootstrap local de démonstration | Donnée locale autonome, construite avec des constantes P02 | Commande console locale | Démonstrations P02 historiques ; aucun lien avec `PropertyAuthoringState` |
| Property Lifecycle state | `Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\*`; tables `publication_workflow` Property | RealEstateCatalog Property Lifecycle | État de workflow (`draft`, `active`, maintenance, unavailable, decommissioned, archived), distinct de l'Aggregate et d'Authoring | `DeterministicPropertyLifecycleOrchestrator` | Événements lifecycle et transport Public Projection Delivery |
| Search Property source | `SearchDiscovery\Domain\Model\PropertyProjectionSource` | SearchDiscovery | Décision Search liée à un Listing : état, facets, revision | Produit par les capacités Search existantes | Source de reconstruction publique ; ne crée pas Property |
| SEO Property source | `ContentSeo\Domain\Model\PropertySeoSource` | ContentSeo | Faits SEO liés au Listing : type, ville, état, revision | Produit par ContentSeo | `CertifiedPublicListingProjectionSource`, politique SEO |
| Projection Property facts | Partie Property de `PublicListingProjectionSources`, `SearchListingProjection` et `PublicListingReadModel` | Public Projection | Copie publique dérivée : `propertyId`, type, surface, rooms, place/city et version dans le watermark | Construite uniquement si toutes les sources certifiées existent | Search Reader, fiche publique, SEO, sitemap |
| Public Listing projection record | `PublicListingProjectionRecord`; table `public_projection.listing_projections` | Public Projection | Read model public final versionné par génération et watermark | `PublicListingProjectionUpdater` / writer | Readers publics ; aucune autorité de création Property |

## Distinctions obligatoires

- `PropertyAuthoringState` n'est pas un Aggregate Property incomplet : son schéma, son ownership et ses règles de replay sont propres à Authoring.
- `PropertyRegistry` est un port vers l'Aggregate, pas un alias vers Authoring.
- `PropertyProjectionSource` et `PropertySeoSource` sont des sources dérivées liées au Listing ; elles ne remplacent pas `RealEstateCatalog\Property`.
- Les facts Property du read model public ne sont créés qu'après résolution de l'Aggregate Property et des autres sources certifiées.

## Écart de forme

Authoring ne porte que type/ville/quartier. L'Aggregate exige également une référence, pièces, salles de bain, année métier, validations de type, et éventuellement une adresse structurée avec place géographique. Cette différence confirme qu'une conversion ne peut être supposée mécanique par Projection.
