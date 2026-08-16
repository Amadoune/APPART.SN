# Decision contract

`PublicGeographyDecision` porte `placeId`, `PublicGeographyRevision`, `locality` non vide et breadcrumb non vide. Chaque item exige label non vide et URL validée.

Le payload canonique JSON contient exactement `locality` puis `breadcrumb[{label,url}]`. La révision doit certifier ce payload par SHA-256. Reader : `Found`, `Missing`, `Corrupted`. Writer : `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent`. Mapper PostgreSQL existant.
