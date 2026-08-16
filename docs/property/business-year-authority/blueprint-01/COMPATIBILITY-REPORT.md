# Compatibility Report

| Boundary | Garantie |
|---|---|
| RealEstateCatalog Domain | BusinessYear et PropertyTypePolicy inchangés |
| Property Authoring | Ne stocke ni ne fournit BusinessYear |
| Public Property Promotion | Utilise son occurredAt stable existant |
| Address Identity | Identité et temps restent indépendants |
| Geography | Aucune dépendance timezone/place |
| Listing Lifecycle | Aucune clock ou règle ajoutée |
| Projection/Search/Public Listing | Jamais consultés |

La stratégie est pure, UTC, sans configuration, migration, ledger ou transaction distribuée. Les commandes existantes peuvent l'adopter sans changer leurs règles Domain.
