# Preuves d’idempotence

Les preuves F6 existantes établissent :

- première commande : `Applied` ;
- même commandId, même instant et même snapshot : `AlreadyApplied` ;
- même commandId avec checksum divergent : `DivergentCommand` ;
- une seule ligne Property et une seule ligne ledger après replay ;
- checksum SHA-256 couvrant contrat, Property, owner, version, snapshot canonique et instant UTC ;
- comparaison d’un Aggregate existant avant toute seconde création.

Le ledger initial et l’Aggregate restent inchangés lors du replay divergent. La recertification terminale globale ne peut toutefois pas être prononcée à cause de la divergence transactionnelle Listing décrite dans `PROMOTION-RECERTIFICATION.md`.
