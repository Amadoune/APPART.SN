# Frontière de la future correction

## Produit autorisé

Un seul composant :

`src/Modules/RealEstateCatalog/Application/Promotion/DeterministicPromoteAuthoredPropertyV1.php`, méthode privée `compatible()`.

La correction ajoute la comparaison stricte AddressId et rend explicites les cas Address null/non-null. Elle ne modifie pas `Address::equals()`.

## Tests ciblés

- Unit Promotion : matrice Address et faits Property, ledger/replay ;
- PostgreSQL Promotion : Property persistée canonique puis non canonique ;
- composition Submit : poursuite uniquement après compatibilité canonique ;
- Architecture : frontières inchangées.

## Interdits

F2, Address Domain, ChangeAddress, RegisterProperty, ledger, Submit, Providers, stores, SQL et migrations. Aucune migration 101.
