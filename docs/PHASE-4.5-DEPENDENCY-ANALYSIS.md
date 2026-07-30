# Phase 4.5 — Dependency Analysis

## Dépendances autorisées

La capacité réutilisera uniquement les fondations génériques certifiées : PostgreSQL Runtime, transaction Aggregate + Outbox, Public Projection Delivery, Worker générique, Runtime Health et conventions HTTP.

## Dépendances interdites

Le workflow de statut ne dépend de :

- aucun Aggregate `Professional` à l'exécution ;
- aucun établissement ni mandat ;
- aucun Listing, Property, Reservation ou Lead ;
- aucun catalogue d'éligibilité ;
- aucune horloge, identité ou source externe.

## Consommateurs futurs

Le statut pourra ultérieurement alimenter une source propriétaire d'éligibilité Advertiser. Cette dérivation n'appartient pas à 4.5 et ne modifie pas la matérialisation Lead 4.4C-S2. Toute connexion exigera un sprint contractuel séparé.

## Owners techniques

- journal, Inbox et Outbox futurs : owner `Professionals` ;
- schéma PostgreSQL pressenti : `professionals` ;
- l'ajout au résolveur Outbox devra être additif ;
- migrations historiques et owners existants restent gelés.
