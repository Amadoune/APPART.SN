# Current Handoff Map

| Mécanisme | Producteur → consommateur | Payload / décision | Transaction, idempotence, replay | Ownership | Promotion Authoring → Property ? |
|---|---|---|---|---|---|
| Property Authoring Store | Authoring Operations → PostgreSQL Authoring | Snapshot complet `PropertyAuthoringState` | Transaction locale, advisory lock, expected version, intent checksum, `AlreadyApplied` | Authoring | Non, persistance interne |
| Property Authoring Catalog Adapter | Property Authoring Store → Listing use cases | `PropertyAvailability` seulement | Read-only, déterministe, sans cache | Authoring autorise une lecture d'éligibilité | Non, aucun fait transféré |
| Property Authoring Media Catalog Adapter | Property Authoring Store → Media | Existence/ownership Property | Read-only | Authoring | Non |
| Listing creation reference | Authoring → Listing Lifecycle | `propertyId` seulement dans `CreateListingDraftCommandV1` | Transaction Listing + intent ledger | Listing devient owner du Listing, pas du Property | Non |
| Authoring Public Fact Handoff | Listing Draft → Listing Lifecycle Public Facts | `transactionKind` uniquement | Préparation à Submit, scellement à Publish, replay/version | Listing Lifecycle | Non, explicitement non-Property |
| Property Aggregate Registry | Property use cases → `PropertyRegistry` | Aggregate `Property` complet | Transaction locale, contraintes uniques, optimistic locking | RealEstateCatalog Domain | Non : aucune entrée depuis Authoring |
| Bootstrap local P02 | `CreateLocalFirstListing` → `Property::register` → `PropertyRegistry::add` | Valeurs locales construites par la commande | Démonstration locale, hors parcours HTTP owner-scoped | Commande locale | Non : ne lit pas `PropertyAuthoringState` et ne constitue pas un handoff Authoring |
| Property lifecycle Outbox | Property Lifecycle → Public Projection Delivery | Identité, états lifecycle, action, version, instants | Aggregate + Outbox atomique, message déterministe | RealEstateCatalog Lifecycle | Non : suppose un lifecycle Property existant |
| Property lifecycle Inbox | Delivery consumer → Property lifecycle inbox | Événement lifecycle canonique | `event_id` unique, checksum, `AlreadyStored` | RealEstateCatalog | Non : stocke un événement, ne crée pas Aggregate |
| Public Property delivery lookup | Property delivery → multi-target propagation | `propertyId` uniquement | Delivery/outbox replay existant | Public Projection consumer | Non : fan-out vers Listings liés à un Property déjà autoritatif |
| Certified Projection Source | Registries/readers → Public Projection Updater | Aggregate Property + Media + Listing + Search + SEO + générations | Read-only assembly puis writer idempotent par watermark | Public Projection | Non : refuse `PropertyMissing` |

## Conclusion

Aucun handoff de production inventorié ne transporte `propertyType`, `city`, `neighborhood` d'Authoring vers `RegisterProperty` ou `PropertyRegistry::add`. La commande locale P02 construit directement un autre Property avec ses propres valeurs de démonstration ; elle ne consomme pas l'état Authoring. Les adapters existants sont des ports de lecture d'éligibilité, pas des autorités de matérialisation.
