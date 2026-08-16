# Compatibility Report

| Boundary | Préservation |
|---|---|
| Geography Domain | Agrégat, PlaceType, hierarchy et lifecycle inchangés |
| Geography lifecycle | enabled/disabled/merged restent seuls décideurs de selectability |
| IAM | aucun rôle ou scope ajouté |
| Property Authoring | consumer read-only ; conserve seulement PlaceId |
| RealEstateCatalog | revalidation `GeographicPlaceCatalog::statusOf` maintenue |
| Public Property Promotion | reçoit une identité explicite, sans résolution de label |
| Public Projection | reader aval distinct inchangé |
| Search/Public Listing | consumers de Projection uniquement |

Le modèle introduit une future capacité de lecture dans Geography Application, sans transaction distribuée, duplication d'Aggregate, fuzzy matching ou nouvelle règle métier Property.
