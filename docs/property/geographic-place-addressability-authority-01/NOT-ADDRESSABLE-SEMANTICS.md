# NotAddressable Semantics

`NotAddressable` signifie : la Place existe, son snapshot est valide, elle est enabled et non merged, mais son niveau géographique est trop large pour constituer la localisation d’une `Address` Property.

Ce statut est distinct de :

- `NotFound` : aucune Place ne correspond à l’identité ;
- `Disabled` : la Place existe mais son lifecycle interdit son utilisation ;
- `Merged` : la Place a été remplacée par fusion, sans redirection automatique ;
- corruption ou indisponibilité : aucun statut métier fiable ne peut être produit.

`NotAddressable` ne signifie ni « libellé invalide », ni « niveau moins précis disponible », ni « PropertyType incompatible ». Il exprime exclusivement le verdict RealEstateCatalog sur un `PlaceType` valide.
