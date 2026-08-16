# Authority Model

| Responsabilité | Owner | Consumer / limite |
|---|---|---|
| Identité, nom, type, parent, lifecycle Place | Geography Domain | Aucun autre module ne les crée |
| Lecture sélectionnable | Geography Application | Reader read-only, DTO minimal |
| Choix humain | Property Authoring UX | Choisit un ID retourné ; ne décide pas sa validité Domain |
| Conservation du choix | Property Authoring | Stocke seulement GeographicPlaceId avec la version Authoring |
| Revalidation avant Aggregate Property | RealEstateCatalog | `GeographicPlaceCatalog::statusOf` reste autoritatif |
| Géographie publique aval | Public Projection | `PublicGeographyDecisionReader`, responsabilité distincte |

Les Places sont des données de référence communes. Le catalogue n'est pas owner-scoped et aucun `ownerAccountId` ne modifie son contenu.
