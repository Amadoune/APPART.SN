# Phase 5.2A — Authoring Operations Foundation

## Statut

**GO CERTIFIÉ — FERMÉ.**

## Opérations

Le contrat public `PropertyListingAuthoringOperations` expose sept opérations
fermées :

- initiation et mise à jour PropertyAuthoring ;
- création complète d’un Listing ;
- mise à jour du draft ;
- ajout et retrait de délégation ;
- soumission contrôlée vers F-01.

Chaque commande porte un `intentId`, l’AccountId auto-scopable, les identités de
ressource, la version attendue, les données fermées et un instant explicite. Son
checksum canonique couvre l’ensemble du contexte.

## Création complète

La création consomme exclusivement la frontière publique certifiée
`CreateListingDraftV1`. Dans une transaction Listing locale, elle fait
converger :

1. l’Aggregate Listing et son journal d’intent ;
2. le draft éditorial ;
3. l’ownership initial immuable ;
4. l’item de portfolio propriétaire.

La transaction existante utilise des savepoints lorsqu’elle participe à une
transaction englobante. Une erreur d’un store Authoring annule l’ensemble des
écritures Listing. PropertyAuthoring est préalablement lu, jamais inclus dans
une transaction ACID multi-owner.

## Handoff F-01

Une soumission vérifie l’ownership ou la permission `SUBMIT`, puis la complétude
du draft. Le handoff appelle uniquement le contrat public
`ListingPublicationOrchestrator` avec `ListingPublicationAction::Submit`.

L’orchestrateur Authoring ne dépend ni de `ListingPublicationWorkflowStore`, ni
d’un repository ou adapter PostgreSQL de F-01. Les résultats F-01 sont traduits
vers une matrice fermée sans réinterpréter le lifecycle.

## Composition

`PropertyListingAuthoringOperationsServiceProvider` ajoute les bindings de la
frontière CreateListing V1, de sa transaction locale, de son journal d’intents
et de l’orchestrateur Operations. L’adaptateur Property se trouve dans
l’enclave de composition `app/Infrastructure`, évitant toute dépendance directe
inter-module.

## Frontières

Aucune modification du Runtime, du HTTP certifié, de F-01, F-02, F-11, F-14,
F-15, F-17 ou F-18. Aucune migration nouvelle ; les migrations 001–057 restent
inchangées.
