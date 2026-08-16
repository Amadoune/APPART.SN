# Rapport de compatibilité

La décision préserve :

- l’égalité descriptive `Address::equals()` ;
- la sémantique `ChangeAddress` ;
- F2 comme unique autorité AddressId ;
- `RegisterProperty` et les règles Domain ;
- le ledger et la transaction F6 ;
- la précondition Submit existante ;
- les Property legacy sans backfill.

Les données persistées actuelles suffisent à la comparaison stricte. Aucun changement de schéma, migration ou règle Domain supplémentaire n’est nécessaire.
