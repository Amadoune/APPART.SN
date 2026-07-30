# Phase 4.7 — Capability Decision

## Décision

Capacité retenue : **Administrative Action Lifecycle**.

Propriétaire unique : **AdministrationAudit**.

## Justification

La capacité gouverne le passage d'une action administrative depuis sa préparation jusqu'à son enregistrement direct ou sa décision indépendante. Elle protège une fonction transverse à forte valeur : la traçabilité des actions sensibles et la séparation des responsabilités.

Le module possède déjà les identités, statuts, politiques, événements historiques, un registre et une persistance PostgreSQL. La Phase 4.7 ne réutilisera pas l'Aggregate à l'exécution du futur workflow et ne modifiera pas ces composants sans amendement versionné.

## Positionnement

La Phase 4.7 succède aux capacités verticales 4.1 à 4.6 gelées. Elle réutilisera uniquement leurs fondations génériques certifiées : PostgreSQL Runtime, transactions, Runtime Health, Public Projection Delivery, Worker générique, Outbox multi-owner et conventions HTTP.

## Exclusions

Sont hors capacité :

- la création de l'action et de son identité ;
- le contenu libre et la taxonomie du motif ;
- l'exécution de la ressource cible ;
- la définition des types d'actions ;
- l'évaluation de la politique four-eyes ;
- les écritures d'audit détaillées historiques ;
- toute orchestration de comptes, rôles, professionnels, listings, paiements ou modération.
