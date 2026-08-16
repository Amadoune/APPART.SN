# Identity Nature

`AddressId` est une identité technique :

- elle n'apparaît dans aucune règle métier d'Address ;
- `Address::equals` compare seulement PlaceId et AddressLine ;
- le Repository la persiste sous la ligne adressée principalement par propertyId ;
- aucune signification publique ou humaine n'est observée.

Son UUID garantit format et stabilité de référence. Il n'implique pas une génération aléatoire. Sa valeur ne code aucun fait métier et n'est jamais affichée comme référence produit.
