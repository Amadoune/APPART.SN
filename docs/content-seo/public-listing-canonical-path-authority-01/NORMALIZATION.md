# Normalization

Représentation exacte :

- chaîne ASCII minuscule ;
- préfixe littéral `annonces/` ;
- UUID canonical `8-4-4-4-12` minuscule ;
- aucun slash initial ou terminal ;
- aucun slash supplémentaire ;
- aucune query string ni fragment ;
- aucun percent-encoding requis ;
- aucun Unicode.

Un ListingId non canonical est rejeté, jamais corrigé implicitement.
