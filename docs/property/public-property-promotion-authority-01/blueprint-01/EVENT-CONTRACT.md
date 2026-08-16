# Event Contract

La stratégie V1 n'introduit **aucun événement de demande de promotion**. La commande est synchrone et `PropertyRegistered` reste l'événement Domain produit par `Property::register` après validation.

Contrat documentaire attendu de l'événement Domain existant :

- nom canonique : `PropertyRegistered` ;
- owner producteur : RealEstateCatalog Domain ;
- version de schéma : à expliciter par l'implémentation si transport durable ;
- `eventId` : identité durable créée par l'infrastructure d'événements ;
- `propertyId` et `aggregateVersion` ;
- `occurredAt` égal à l'instant Domain de la commande ;
- payload : faits de l'Aggregate, sans owner IAM ni données Projection ;
- consommateurs : Property Lifecycle et intégrations explicitement bindées ; Projection ne l'utilise pas pour synthétiser la source ;
- replay : idempotent par eventId/aggregateVersion.

Ce Blueprint ne crée ni ne modifie ce contrat PHP. L'absence actuelle d'une outbox Domain Property doit être qualifiée lors de l'implémentation si la durabilité de l'événement est requise.
