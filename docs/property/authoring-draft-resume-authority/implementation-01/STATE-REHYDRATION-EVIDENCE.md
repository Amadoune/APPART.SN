# State Rehydration Evidence

Le snapshot restaure depuis les stores autoritatifs :

- PropertyId et ListingId ;
- Property Authoring version et Listing Draft version ;
- versions Aggregate et Workflow ;
- transaction, type, référence, surface, pièces, salles de bain, année et adresse ;
- titre, description, prix, charges, disponibilité et préférence de contact ;
- Geography et metadata Media ;
- step dérivé.

En mode Resume, `authoring.js` n'appelle pas `crypto.randomUUID()`. Les mutations ultérieures éventuelles utiliseraient `update-property` et `update-draft` avec les versions restaurées ; aucune mutation n'est déclenchée par le chargement.
