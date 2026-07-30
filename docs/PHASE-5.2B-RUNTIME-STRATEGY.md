# Phase 5.2B — Runtime Strategy

## Composition future

Une composition propriétaire `MediaIngestionRuntimeV1` devra exposer des ports
fermés pour Upload, Asset, Processing et Quota. Les adapters de stockage,
détection, décodage et scan resteront en Infrastructure.

## Politiques

- fail-closed si IAM, ownership, quota, scan ou stockage ne sont pas
  déterminables ;
- bindings lazy et owner-scoped ;
- diagnostics internes distincts des résultats publics ;
- aucun ajout au catalogue Runtime Health F-14 sans amendement ;
- workers bornés, leases, retry différé et quarantaine terminale ;
- recette de transformation et normalisation versionnées ;
- horloge, IDs et entropy injectés par ports.

## Atomicité

L’upload, le store objet et PostgreSQL ne partagent pas une transaction ACID.
La cohérence repose sur états explicites, intents, leases, compensation et
reconciliation. Le rattachement F-06 demeure une opération séparée derrière
la future frontière publique certifiée.
