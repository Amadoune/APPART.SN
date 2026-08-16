# RegisterProperty Requirements

`RegisterProperty::execute` exige toujours des objets pour id, référence, type, rooms, bathrooms, business year et instant. Surface, construction year et Address sont typés nullable, mais leur obligation dépend de `PropertyTypePolicy`.

| Donnée | Nature | Obligation réelle |
|---|---|---|
| PropertyId | identité Domain | Toujours requise |
| PropertyReference | identité métier unique | Toujours requise |
| PropertyType | fait métier | Toujours requis |
| SurfaceArea | fait physique nullable | Requise sauf `Other` |
| RoomCount | fait physique non nullable | `0` pour Land ; au moins `1` pour Apartment, House, Villa, Office, Commercial ; Other selon cohérence bathrooms |
| BathroomCount | fait physique non nullable | `0` pour Land ; valeur requise pour tous les types ; `<= rooms` pour résidentiel, Office et Other |
| ConstructionYear | fait physique nullable | Optionnelle ; obligatoirement `null` pour Land ; jamais future par rapport au business year |
| Address | valeur structurée nullable | Requise sauf `Other` |
| GeographicPlaceId | partie d'Address | Requis lorsqu'Address existe ; doit être `Usable` |
| AddressId | identité technique d'Address | Requise lorsqu'Address existe |
| AddressLine | fait owner-authored | Requise lorsqu'Address existe |
| BusinessYear | contexte de validation | Toujours requis à l'appel, non persisté comme fait Aggregate |
| occurredAt | instant de création | Toujours requis, persiste `lastChangedAt` et événement |

Le Domain initialise status `Active` et version `0`; ces valeurs ne sont pas des entrées Authoring.
