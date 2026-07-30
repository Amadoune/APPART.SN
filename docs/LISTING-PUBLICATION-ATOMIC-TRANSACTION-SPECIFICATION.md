# Listing Publication Atomic Transaction Specification

## Contrat

`ListingPublicationAtomicTransaction::run(Closure)` constitue la frontière atomique de 4.1E. La même connexion PDO PostgreSQL encadre :

1. la lecture et l'écriture du journal de publication ;
2. la création déterministe des messages ;
3. leur insertion dans l'Outbox existante.

La transaction est validée uniquement si toute l'opération réussit. Toute exception annule ensemble la transition et les messages Outbox.

## Implémentation Runtime

Le port est un alias de `PostgreSqlAggregateOutboxTransaction`, déjà certifié. Sa résolution est paresseuse. Le bootstrap ne démarre aucune transaction, ne lit aucun état et ne produit aucun événement.

## Interdictions

Aucune transaction imbriquée, stratégie parallèle, compensation, écriture après commit, horloge ou génération aléatoire n'est autorisée.
