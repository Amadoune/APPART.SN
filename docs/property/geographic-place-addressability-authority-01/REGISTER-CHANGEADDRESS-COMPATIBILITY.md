# Register / ChangeAddress Compatibility

`RegisterProperty` et `ChangeAddress` consomment déjà le même port `GeographicPlaceCatalog` et appliquent la même décision : seul `GeographicPlaceStatus::Usable` permet de poursuivre.

Tous les autres statuts provoquent `UnavailableGeographicPlace` sans changement d’exception Domain :

- `NotFound` ;
- `Disabled` ;
- `Merged` ;
- `NotAddressable`.

La policy est donc unique et ne varie ni selon le use case, ni selon l’Aggregate courant. À type et état Geography identiques, Register et ChangeAddress obtiennent nécessairement le même statut.

La consultation ne se produit dans Register que lorsqu’une Address est fournie. ChangeAddress reçoit nécessairement une Address et la revalide avant toute mutation de Property.
