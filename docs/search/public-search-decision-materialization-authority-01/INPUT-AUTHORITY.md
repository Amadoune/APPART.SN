# Autorité des entrées

| Entrée | Contrat existant | Identité | Révision disponible | Owner | Disponibilité après Published |
|---|---|---|---|---|---|
| Listing | `ListingRegistry`, événement `ListingPublished` | ListingId | Aggregate v3, révision Published séquence 4, événement publication v4 | ListingLifecycle | oui |
| Property | `PropertyRegistry` | PropertyId | Aggregate v0 et promotion authoring v1 | RealEstateCatalog | oui, mais mapping vers `SourceRevision` non qualifié |
| Media | `MediaCollectionRegistry`, ownership lookup | CollectionId/PropertyId | collection v1, média principal actif | Media | oui |
| transaction publique | `AuthoringPublicFactHandoffV1` | ListingId | authoring v1, published revision id | ListingLifecycle | oui, hors rang |

Les contrats SearchDiscovery attendus sont `ListingCatalog`, `PropertyCatalog` et `MediaCatalog`, qui doivent retourner respectivement `ListingProjectionSource`, `PropertyProjectionSource` et `MediaProjectionSource`. Aucun binding productif de ces trois ports SearchDiscovery n'a été trouvé.

Public Projection est explicitement exclue comme entrée.

Les faits RC2 existent, mais ne suffisent pas à construire une décision conforme : le rang n'a pas de source normative et la Property canonique v0 ne peut être copiée directement dans `SourceRevision`, qui exige une version positive.

## Completion 01

Les deux lacunes sont fermées : ranking policy v1 fournit rang `0`/facettes `[]`; la révision Property provient du record de promotion RealEstateCatalog (`authoringVersion`, `commandId`, `occurredAt`), jamais de l'Aggregate v0.
