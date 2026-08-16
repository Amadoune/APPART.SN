# Preuve de canonicalité Address

L’AddressId attendu est issu exclusivement de la chaîne certifiée `PropertyId + AddressIntentId → AddressIdentityIssuerV1 → AddressId`.

Pour une Property ledgerless, la compatibilité exige :

- même présence d’adresse ;
- même AddressId ;
- même GeographicPlaceId ;
- même AddressLine.

Le cas canonique retourne `AlreadyApplied` sans mutation ni ledger de rattrapage. Un AddressId différent avec les mêmes faits descriptifs retourne `DivergentCommand`, conserve l’Aggregate et bloque Submit.

`Address::equals()`, ChangeAddress et RegisterProperty restent inchangés.
