# Phase 5.2A — Ownership Matrix

| Autorité | Aggregate/Projection owner | Écrit | Références seulement |
|---|---|---|---|
| Property | RealEstateCatalog | faits physiques, adresse, référence, version | Place |
| Property Lifecycle | RealEstateCatalog, F-02 | état lifecycle certifié | Listing |
| Property Authoring | RealEstateCatalog.Authoring | owner Account, permissions locales, intent et lien Property | AccountId, PropertyId |
| Listing | ListingLifecycle | identité, PropertyId, état et révisions lifecycle | PropertyId |
| Listing Publication | ListingLifecycle, F-01 | workflow certifié | Property/Media availability |
| Listing Authoring Draft | ListingLifecycle.Authoring | contenu privé, complétude, version, historique éditorial | ListingId, PropertyId |
| Listing Ownership | ListingLifecycle.Authoring | titulaire et délégations propres à l'annonce | AccountId, ListingId |
| Authoring Portfolio | ListingLifecycle.Authoring read side | projection privée reconstruisible | IDs et états minimisés |

## Règles d'unicité

- un `PropertyId` possède exactement un owner authoring actif ;
- un `ListingId` possède exactement un titulaire ;
- un draft authoring appartient à exactement un `ListingId` ;
- une délégation est unique par `(ListingId, AccountId, permission)` ;
- aucun owner ne peut être déduit d'une donnée HTTP fournie par le client ;
- `AuthoringPortfolio` n'accepte aucune commande métier.

## Event, Projection, Outbox et HTTP owners

| Type | Owner unique |
|---|---|
| événements Property Authoring | RealEstateCatalog.Authoring |
| événements Listing Authoring | ListingLifecycle.Authoring |
| projection Authoring Portfolio | ListingLifecycle.Authoring |
| future Outbox Property Authoring | RealEstateCatalog.Authoring |
| future Outbox Listing Authoring | ListingLifecycle.Authoring |
| HTTP Property Authoring | adapter RealEstateCatalog.Authoring |
| HTTP Listing/Portfolio | adapter ListingLifecycle.Authoring |

L'Outbox, le transport et HTTP ne sont pas implémentés pendant Discovery.
