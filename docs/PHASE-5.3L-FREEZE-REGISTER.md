# Phase 5.3L — Registre de gel proposé

## Éléments proposés au gel

| Élément | Owner | Version/namespace | Invariant | Preuve | Statut |
|---|---|---|---|---|---|
| six Commands et résultats fermés | ModerationReports | `ModerationOrchestration\Contract` V1 | catalogues fermés, quatre yeux | 5.3B/5.3F | GEL PROPOSÉ |
| quatre Queries et DTO | ModerationReports | HTTP Read Boundaries V1 | read-only, fail-closed | 5.3J | GEL PROPOSÉ |
| Events, payloads, enveloppes | ModerationReports | Event V1 | identité/checksum déterministes | 5.3G/5.3K | GEL PROPOSÉ |
| Persistence et Queue | ModerationReports | migrations 063–070 | owner-local, optimistic locking | 5.3D/5.3E | GEL PROPOSÉ |
| Routing et destinations | ModerationReports | catalogue fermé | aucune destination dynamique | 5.3G | GEL PROPOSÉ |
| Delivery et Outbox | ModerationReports | migrations 065–067 | append-only, retry/replay | 5.3G/5.3H | GEL PROPOSÉ |
| Runtime et bindings | ModerationReports | Runtime V1 | singleton, lazy, fail-closed | 5.3E/5.3K | GEL PROPOSÉ |
| HTTP & Security | ModerationReports | HTTP 5.3J | auto-scope, IAM, no-store | 5.3J | GEL PROPOSÉ |
| Moderator Authorization | IAM | Reader V1 | read-only, fail-closed | amendement IAM | GEL PROPOSÉ |
| Listing Moderation | Listing | Reader/Gateway V1 | F-01 seule autorité | amendements Listing | GEL PROPOSÉ |
| Audit Append | AdministrationAudit | Append V1, migration 071 | append-only, idempotent | 5.3K | GEL PROPOSÉ |
| ownership et transactions | owners respectifs | matrices 5.3 | aucune transaction distribuée | Architecture/PostgreSQL | GEL PROPOSÉ |
| retry/replay/quarantaine | ModerationReports/AdminAudit | politiques V1 | convergence bornée | 5.3G–5.3K | GEL PROPOSÉ |
| matrices de certification | Gouvernance | 5.3A–5.3L | traçabilité | dossier final | GEL PROPOSÉ |

## Hors gel

- Media, Account et Professional hors frontières explicitement consommées ;
- fonctionnalités futures et phases ultérieures ;
- amendements futurs ouverts explicitement par l'autorité ;
- documents historiques conservés en lecture seule ;
- toute capacité ne relevant pas de Moderation & Reports.

Ce registre prépare un gel. Il ne le prononce pas.
