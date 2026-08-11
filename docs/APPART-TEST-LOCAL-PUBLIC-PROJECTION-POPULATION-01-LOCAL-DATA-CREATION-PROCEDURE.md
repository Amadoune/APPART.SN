# APPART.TEST LOCAL PUBLIC PROJECTION POPULATION 01 — Local Data Creation Procedure

## Statut

`BLOCKED — aucune procédure exécutable conforme disponible`

La procédure locale ne peut pas être exécutée avec les surfaces actuellement exposées. Les composants existent individuellement, mais aucune commande ou composition certifiée ne garantit l'enchaînement complet sans SQL ad hoc ni reconstruction manuelle d'un état publié.

## Procédure qualifiée pour un futur jalon autorisé

1. vérifier que l'environnement est `local` et que la cible est exclusivement `appart_test` ;
2. créer un Property de développement via le contrat Application certifié ;
3. créer un Listing draft via le contrat de création certifié ;
4. compléter l'authoring via les opérations certifiées ;
5. exécuter Submit, BeginReview puis ApproveAndPublish via l'orchestrateur de publication et les autorités prévues ;
6. produire Search, Content/SEO, Geography et Media via leurs writers certifiés ;
7. créer une génération candidate via `PublicProjectionGenerationManager` ;
8. reconstruire la projection via `PublicProjectionRebuilder` ;
9. valider le manifeste puis activer la génération via `PublicProjectionGenerationManager` ;
10. vérifier le Reader et les deux routes HTTP publiques.

Cette séquence est une qualification documentaire. Elle n'a pas été exécutée et aucun identifiant de donnée n'a été réservé.

## Garde-fous requis

- commande indisponible hors environnement local ;
- marqueur explicite de donnée de développement ;
- aucune écriture SQL directe ;
- aucune construction directe d'un Aggregate dans un état terminal ;
- arrêt immédiat sur toute transition refusée ;
- journal des résultats typés de chaque writer ;
- nettoyage par les mêmes frontières applicatives.
