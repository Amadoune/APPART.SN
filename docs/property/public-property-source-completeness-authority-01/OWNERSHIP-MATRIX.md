# Ownership Matrix

| Donnée/capacité | Source owner | Validation owner | Stockage/version |
|---|---|---|---|
| Identité/owner/version Authoring | Property Authoring + IAM | Authoring | PropertyAuthoringStore |
| Référence proposée | Propriétaire via Authoring | RealEstateCatalog Domain/Registry | snapshot puis Aggregate |
| Type et faits physiques | Propriétaire via Authoring | Value Objects + PropertyTypePolicy | snapshot puis Aggregate |
| AddressLine | Propriétaire via Authoring | AddressLine/Domain | snapshot puis Aggregate |
| GeographicPlaceId | Geography, sélectionné par propriétaire | Geography lifecycle + GeographicPlaceCatalog | snapshot puis Address |
| AddressId | Autorité technique RealEstateCatalog à qualifier | AddressId | non résolu |
| BusinessYear | Autorité calendrier RealEstateCatalog Application à qualifier | BusinessYear/Policy | contexte, non persisté Aggregate |
| Aggregate et événements | RealEstateCatalog Domain | RegisterProperty | PropertyRegistry |
| Projection publique | Public Projection | sources certifiées | projection versionnée |

Projection, Listing, Media, Search et SEO ne possèdent aucune de ces sources Property.
