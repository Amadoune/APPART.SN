# Matrice source vers Aggregate

| Source | Autorité | Transformation | Destination | Preuve disponible |
|---|---|---|---|---|
| `propertyId` du Listing Draft | Listing Authoring | `PropertyId` | Property.id | test PostgreSQL Submit F6 |
| owner de commande | session/orchestration IAM, vérifié contre Authoring | comparaison fermée | contrôle Promotion | tests owner mismatch |
| version Authoring | commande de concurrence | égalité stricte | contrôle Promotion | tests VersionConflict |
| type/référence/faits physiques | snapshot F4 | Value Objects Domain | Aggregate Property | tests Unit et PostgreSQL Promotion |
| `propertyId + addressIntentId` | F2 `AddressIdentityIssuerV1` | UUIDv5 déterministe | `Address.id` | tests F2 et Promotion |
| `occurredAt` | F3 `BusinessYearAuthorityV1` | année civile UTC | validation `RegisterProperty` | tests F3 et composition Promotion |
| `geographicPlaceId` | F5-A productif sur registre F0 | statut courant | `Address.placeId` | tests Geography productifs |
| ligne d’adresse | snapshot F4 | `AddressLine` | `Address.line` | test PostgreSQL Promotion |
| commande | Promotion | checksum canonique | ledger migration 100 | tests replay/divergence/atomicité |

Aucune source Projection, Search, Public Listing ou HTTP Geography n’intervient dans le mapping.
