# Listing Publication PostgreSQL Specification

`PostgreSqlListingPublicationWorkflowRepository` implémente lecture et écriture du contrat applicatif dédié. Il accepte un `ListingPublicationTransition` déjà décidé.

La table `listing_lifecycle.publication_workflow_transitions` utilise la clé `(listing_id, version)`. Une lecture courante est exacte, ordonnée par version descendante et limitée à une ligne. La version est un entier métier strictement positif; aucun timestamp ne participe à l'ordre.

Écriture : verrou par Listing, lecture courante verrouillée, contrôle de version et continuité, mapping mécanique, insertion. Une réémission identique rend AlreadyApplied. Un écart de version, état source divergent ou triplet non certifié produit un résultat typé sans mutation.
