# Phase 5.2B — MediaAsset Contract

## États

`Quarantined → Inspecting → Ready`

`Quarantined|Inspecting → Rejected → PurgePending → Purged`

`Ready → PurgePending → Purged` uniquement si la politique de rétention et les
références certifiées l’autorisent.

## Commands internes

- `RegisterReceivedAssetV1`
- `BeginInspectionV1`
- `AcceptAssetV1`
- `RejectAssetV1`
- `ScheduleAssetPurgeV1`
- `ConfirmAssetPurgedV1`

Chaque commande retourne `Applied`, `AlreadyApplied`, `DivergentIntent`,
`InvalidState`, `VersionConflict` ou `TemporarilyUnavailable`.

## Query

`GetAssetReadinessV1` retourne `Pending`, `Ready`, `Rejected`, `Purged` ou
`Unavailable`. Pour `Ready`, seules les métadonnées nécessaires au handoff sont
exposées : assetId, checksum SHA-256 réel, type fermé, dimensions, taille et
recipeVersion. Aucun object key ni diagnostic de scan.

## Invariants

- checksum, type, dimensions et taille sont calculés côté serveur ;
- le canonique est immuable après Ready ;
- un checksum identique ne fusionne pas implicitement des owners ou droits ;
- Ready exige scan conforme, décodage complet et variantes obligatoires ;
- Rejected et Purged sont terminaux ;
- la suppression d’un objet est confirmée, jamais déduite d’une requête envoyée.
