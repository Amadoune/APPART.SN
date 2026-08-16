# Compatibility Report

| Boundary | Garantie |
|---|---|
| Property Authoring | Ajout futur d'une intention serveur stable ; client ne fournit pas AddressId |
| Geography | PlaceId sélectionné et revalidé, aucune modification |
| RealEstateCatalog Domain | AddressId/Address/RegisterProperty inchangés |
| ChangeAddress | Nouvelle identité seulement pour nouveaux faits physiques |
| Listing Lifecycle | Aucune responsabilité d'identité |
| Public Property Promotion | Compose l'Issuer avant RegisterProperty |
| Projection/Search/Public Listing | Consumers uniquement, aucune émission |

La stratégie ne requiert ni migration/ledger, ni transaction distribuée, ni valeur fictive. Elle utilise un format UUID accepté par le Value Object existant.
