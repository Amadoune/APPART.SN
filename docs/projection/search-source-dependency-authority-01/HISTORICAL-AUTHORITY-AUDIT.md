# Audit des autorités historiques

## Projection Runtime Source

`PROJECTION-RUNTIME-SOURCE-ANALYSIS`, `ASSEMBLY` et `DEPENDENCY-MATRIX` certifient la décision Search comme source obligatoire et précisent son usage : **version finale uniquement**. L'assembleur ne construit aucune décision Search.

## Search Decision Read

`SEARCH-DECISION-READ-ANALYSIS` et `SEARCH-DECISION-READER` établissent SearchDiscovery comme owner, le store spécialisé durable, les statuts `Found/Missing/Corrupted` et l'absence de recalcul par le reader.

## Search Experience

La First Foundation qualifie les faits Listing/Property/Media comme entrées publiques versionnées et SearchDiscovery comme owner de la décision, du journal et de l'index. Elle n'a toutefois créé aucun consumer productif de ces faits.

## Publication Review F3

F3 certifie `ProjectPublishedListingV1 → PublicListingProjectionUpdater` et réduit une source indisponible en `NotReady`. Il interdit l'écriture Search directe depuis PublicationReview.

## Population locale historique

L'audit de population publique signalait déjà `PostgreSqlSearchDecisionWriter` présent mais sans commande locale. Deux commandes locales ont ensuite matérialisé des décisions pour leurs fixtures certifiées; elles ne constituent pas un pipeline productif générique.

## Pourquoi Search est obligatoire

Le watermark public est vectoriel et exige une version positive de chaque décision owner. La décision historique a inclus Search pour empêcher une Projection publique dont l'état Search final serait absent, obsolète ou non traçable. Aucun document audité n'autorise la suppression silencieuse de cette dimension.
