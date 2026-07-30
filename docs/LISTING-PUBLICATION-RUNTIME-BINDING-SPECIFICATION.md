# Listing Publication Runtime Binding Specification

## Bindings certifiables

- `ListingPublicationWorkflow` : singleton paresseux ;
- `ListingPublicationWorkflowMapper` : singleton paresseux ;
- `PostgreSqlListingPublicationWorkflowRepository` : singleton paresseux ;
- `ListingPublicationWorkflowStore` : alias du repository PostgreSQL ;
- `PDO` : connexion PostgreSQL Runtime existante.

L'alias et la classe concrète doivent retourner la même instance. Le mapper injecté doit être l'instance déclarée par le conteneur.

## Interdictions

La composition n'appelle jamais `decide`, `initialize`, `append` ou `read`. Aucun Fake, Null Object, fallback, binding HTTP, Outbox ou Projection n'est admis.

## Effets du bootstrap

L'enregistrement des bindings est sans requête SQL et sans mutation. La connexion n'est demandée qu'au moment où le repository est effectivement résolu.
