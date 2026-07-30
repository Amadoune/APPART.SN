# Lead Eligibility Idempotence and Divergence Policy

Le checksum SHA-256 porte, dans un ordre fixe : Listing, version, relation normative, décision Listing, Advertiser évalué, décision Advertiser, identifiant de cohérence et instant UTC canonique.

- checksum identique à version identique : `AlreadyMaterialized` ;
- relation différente à version identique : `RelationDivergence` ;
- autre contenu différent à version identique : `DivergentVersion` ;
- deux writers identiques concurrents convergent vers `Created` et `AlreadyMaterialized` ;
- deux relations concurrentes divergentes convergent vers `Created` et `RelationDivergence`.

Aucune ligne existante n'est mise à jour.
