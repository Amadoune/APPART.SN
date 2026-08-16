# Preuves d’idempotence

Preuves F6 acquises avant la divergence :

- première Promotion : `Applied` ;
- même commandId/checksum/snapshot/version/instant : `AlreadyApplied` ;
- même commandId avec instant/checksum différent : `DivergentCommand` ;
- aucune seconde Property ;
- aucune seconde ligne ledger.

La qualification de l’Aggregate déjà existant ne peut pas être achevée : l’AddressId n’entre pas dans la comparaison de compatibilité actuelle.
