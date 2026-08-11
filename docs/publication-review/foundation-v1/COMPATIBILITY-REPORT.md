# Compatibility Report

## Compatibilité des capacités gelées

| Capacité | Impact décidé |
|---|---|
| Listing Lifecycle | aucun changement de règle, transition, store ou Aggregate |
| Public Projection | consumer et delivery inchangés ; aucun claim partagé |
| Search | aucun accès ni changement |
| IAM | aucune règle ou implémentation modifiée |
| P05 | parcours jusqu'à Submitted inchangé |

## Migration

Une migration additive PublicationReview et un rollback associé sont nécessaires pour rendre persistants queue, claims, versions et ledger de commandes. La migration ne doit modifier aucune table Lifecycle ou Projection.

Le rollback supprime uniquement les objets owner-scoped créés par PublicationReview, dans l'ordre inverse de leurs dépendances. Il ne touche ni aux événements source ni aux deliveries Projection.

## Données historiques

Il n'y a **aucun backfill implicite**. Les événements produits après activation du handoff alimentent la file.

Les Listings déjà `Submitted` avant activation restent valides dans Lifecycle mais ne sont pas inventés dans la queue. Leur reprise éventuelle requiert une décision distincte et une source événementielle historique démontrable ; aucun scan SQL, état par défaut ou reconstruction depuis Projection n'est autorisé.

## Déploiement

L'ordre compatible est :

1. appliquer la persistance PublicationReview ;
2. activer son consumer indépendant ;
3. activer le fan-out des nouveaux événements Submitted ;
4. vérifier l'ingestion et le replay ;
5. ouvrir seulement ensuite les contrats/commands F1.

Un rollback désactive d'abord le fan-out/consumer, puis retire la persistance additive. Public Projection continue sans interruption.

## Conclusion

La décision est additive, owner-scoped et compatible. Elle ne donne aucune autorisation d'implémentation par elle-même.
