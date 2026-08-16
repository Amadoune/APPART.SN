# Consistency Invariants

Avant `Available`, le Reader vérifie :

- Portfolio.listingId = Draft.listingId = Ownership.listingId = Aggregate.id = Workflow.listingId ;
- Portfolio.propertyId = Draft.propertyId = Ownership.propertyId = Aggregate.propertyId = PropertyAuthoring.propertyId ;
- AccountId autorisé par Ownership et cohérent avec Property owner ;
- Portfolio.draftVersion = Draft.version ;
- Aggregate.status = draft et Aggregate.version = 0 pour le parcours RC2 initial ;
- Workflow.state = draft et version cohérente avec son store ;
- Property/Draft complets selon leurs autorités ;
- collection Media rattachée à la même Property ;
- GeographicPlaceId existant, non merged, enabled et addressable.

Une divergence retourne `StateConflict` ou `Corrupted`. Aucun alignement, patch ou projection silencieuse n’est autorisé.
