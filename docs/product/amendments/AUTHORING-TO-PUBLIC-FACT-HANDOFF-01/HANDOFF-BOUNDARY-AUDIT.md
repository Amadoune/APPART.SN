# Handoff Boundary Audit

## Frontière actuelle

`DeterministicPropertyListingAuthoringOperations` persiste un `ListingDraftState` complet. Lors de Submit, il vérifie la complétude et appelle seulement `ListingPublicationOrchestrator` avec `listingId`, action et version attendue. Aucun fait du draft ne franchit cette frontière.

`CreateListingDraftCommandV1`, l'Aggregate Listing, ses snapshots et ses événements ignorent également `transactionKind`. La projection n'a donc aucune source autorisée pour cette valeur.

## Options comparées

### A. Authoring → Listing Lifecycle → Projection

Option retenue. Authoring produit une enveloppe canonique au Submit. Listing Lifecycle l'accepte comme faits candidats liés au Listing et à la version Authoring. À la publication, Listing Lifecycle scelle la transaction dans la version publiée. La projection lit ensuite uniquement la source Listing owner-scoped.

### B. Authoring → Event → Listing → Projection

Non retenue comme frontière principale. Un événement asynchrone introduirait une fenêtre où Submit ou Publish peut avancer sans les faits candidats. Il exigerait une orchestration de convergence supplémentaire et compliquerait l'atomicité. Un événement peut être émis après acceptation, mais ne doit pas constituer l'autorité du handoff.

### C. Projection → Authoring

Rejetée. Cela relirait Authoring tardivement, transférerait une décision au builder et rendrait les rebuilds dépendants d'un état mutable.

## Ownership

- Avant Submit : Authoring possède la transaction mutable du draft.
- Après acceptation du handoff : Listing Lifecycle possède une copie candidate figée, identifiable par version et checksum ; Authoring conserve son historique mais ne peut modifier cette candidate implicitement.
- À `ApproveAndPublish` : Listing Lifecycle scelle la transaction comme fait de la révision publiée.
- Après publication : Public Projection la propage mécaniquement depuis Listing Lifecycle.

La donnée est reproduite comme snapshot de frontière, mais l'autorité n'est jamais simultanée : draft mutable et fait publié n'ont ni le même statut ni le même cycle de vie.
