# Legacy Migration & Reconciliation — Reconciliation Strategy

## Axes de rapprochement

| Axe | Preuve attendue | Critère de blocage |
|---|---|---|
| Fonctionnel | disposition et règle par ressource ; états cibles acceptés par l'owner | décision inventée, owner absent, cas critique non arbitré |
| Volumétrique | source = conservé + nettoyé + fusionné + archivé + supprimé + quarantaine + erreurs, avec doubles comptes explicités | tout écart inexpliqué |
| Référentiel | relations Legacy résolues vers des IDs owners stables ; aucune ressource active orpheline | FK logique manquante, collision ou correspondance ambiguë |
| Temporel | timezone source documentée, UTC canonique cible, ordre et date d'effet préservés | chronologie impossible ou timestamp interprété sans règle |
| Identifiants | bijection ou fusion explicitement enregistrée ; anciens IDs immuables et traçables | collision cible, réutilisation ou perte de correspondance |

## Quarantaine

La quarantaine est obligatoire pour toute donnée ambiguë, contradictoire, orpheline, interdite, non déterminée ou dépourvue de finalité. Elle conserve une référence opaque vers l'extraction, le domaine, la règle, le motif, la gravité, le niveau de confiance, l'owner attendu et la décision requise. Elle ne rend aucune donnée active et ne devient jamais une source métier.

Les payloads de quarantaine devront être minimisés, chiffrés selon leur sensibilité, soumis à une durée de conservation et accessibles uniquement aux rôles habilités. Aucune PII ne doit apparaître dans les rapports volumétriques ou de pilotage.

## Rapports obligatoires

- manifeste de source et empreinte ;
- volumes par domaine, disposition, règle et statut ;
- correspondances d'identifiants et fusions ;
- orphelins et collisions ;
- écarts temporels ;
- quarantaine par gravité et owner ;
- comparaison entre répétitions ;
- delta de cutover ;
- décisions de rollback/reprise.

## Gates

Une répétition est acceptable uniquement si la totalité du périmètre est expliquée, les écarts critiques sont nuls, les erreurs bloquantes sont closes, les échantillons par owner sont approuvés et les rapports sont reproductibles. Deux répétitions successives doivent être stables avant tout cutover.
