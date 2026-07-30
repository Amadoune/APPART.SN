# Stratégie PostgreSQL — Active Generation Reader

## Requête

La lecture cible `public_projection.generations` avec le prédicat exact `state = 'active'`, un ordre déterministe sur `generation_id` et `LIMIT 2`.

L'index partiel unique certifié en 3.6D sert cette lecture et garantit normalement une seule ligne. La limite à deux permet néanmoins de signaler une incohérence au lieu de masquer une génération supplémentaire.

## Transactions et concurrence

La requête ne prend aucun verrou d'écriture. Sous MVCC PostgreSQL, chaque appel observe un état durable cohérent avant ou après une bascule atomique. Les lectures concurrentes ne modifient aucune ligne et retournent la même génération pour un même snapshot durable.

## Schéma et rollback

Aucune migration n'est créée. Il n'existe donc aucune évolution de schéma ni stratégie de rollback propre à 3.8F. Retirer l'adaptateur n'affecterait aucune donnée et aucune fondation certifiée.
