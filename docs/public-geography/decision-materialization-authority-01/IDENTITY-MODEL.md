# Identity model

L'identité logique et la PK sont le `placeId` owner. Il n'existe ni decisionId, ni relation ListingId/PropertyId dans la décision durable, ni UUID supplémentaire.

ListingId est seulement une identité d'entrée possible pour résoudre le Place. Un replay du même Place cible la même ligne.
