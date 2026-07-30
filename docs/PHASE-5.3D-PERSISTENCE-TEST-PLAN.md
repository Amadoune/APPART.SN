# Phase 5.3D — Persistence Test Plan

## Scénarios PostgreSQL ciblés

| Scénario | Preuve |
|---|---|
| migration | huit tables owner présentes |
| rollback | schéma supprimé puis recréé |
| insert | premier save `Applied` |
| update | version suivante `Applied` |
| optimistic locking | version obsolète `VersionConflict` |
| idempotence | même intent/checksum `AlreadyApplied` |
| divergence | même intent/checksum différent `DivergentIntent` |
| mapper/round-trip | état sauvegardé égal à l'état lu |
| append-only | révisions antérieures conservées |
| décision | lecture identifiée et courante |
| rollback transactionnel | aucune ligne partielle |
| savepoint | transaction englobante conservée |
| advisory lock | deux processus sérialisés |
| concurrence | un `Applied`, un `VersionConflict` |
| queue | project/claim/lease conflict déterministes |
| checkpoint | monotonie et rejeu |

## Architecture

- Application sans Infrastructure, framework ou SQL ;
- migration owner-scoped ;
- aucune FK/cascade cross-domain ;
- aucune inscription Runtime/HTTP ;
- aucune table Outbox ;
- allowlist centrale limitée à l'enclave PostgreSQL ModerationReports.

## Campagnes

- PostgreSQL ciblé ;
- PostgreSQL complet direct avec timeout suffisant ;
- Architecture ciblée et complète ;
- PHPStan ;
- Pint ;
- `git diff --check`.

La suite applicative complète n'est pas une preuve normative demandée pour ce
sprint de Persistence. Toute observation externe est consignée sans modifier
le domaine concerné.
