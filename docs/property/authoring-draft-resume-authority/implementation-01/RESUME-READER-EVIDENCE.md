# Resume Reader Evidence

Le Reader compose exclusivement les lectures certifiées suivantes : Portfolio, Property Authoring, Listing Draft, Ownership, Aggregate, Workflow, Media et Place Registry.

Invariants vérifiés avant `Available` :

- le listing appartient au Portfolio du compte issu de la session ;
- ownership owner ou délégation `EDIT` ;
- cohérence `listingId → propertyId` entre Portfolio, Draft, Ownership et Aggregate ;
- versions Portfolio/Draft/Ownership cohérentes ;
- Aggregate et Workflow tous deux `Draft` ;
- Geography résoluble ;
- Media lisible owner-scoped.

Les erreurs structurelles et indisponibilités sont réduites fail-closed. Aucun SQL, write store, Projection, Search ou Promotion n'est présent dans l'Application Reader.
