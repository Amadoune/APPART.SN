# Phase 5.2B — Persistence Foundation

## Statut

**GO CERTIFIÉ — FERMÉ.**

## Périmètre réalisé

- états Application propriétaires pour MediaUpload, MediaAsset,
  MediaProcessing et MediaQuota ;
- quatre ports de persistence indépendants ;
- mapper déterministe de snapshots JSON structurés ;
- quatre stores PostgreSQL owner-scoped ;
- historique permanent des intents par owner ;
- optimistic locking, advisory locks et participation transactionnelle ;
- contrat PHP public `AttachReadyMediaAssetV1` ;
- journal d’intents Attachment owner-scoped ;
- migrations additives 058 et 059 ;
- tests Unit, Architecture et PostgreSQL ciblés.

## Frontières

Aucun Runtime, Provider, HTTP, Event PHP, Delivery ou Outbox n’est introduit.
Les migrations 004, 007, 031, 032 et 001–057 sont inchangées. Aucune FK,
cascade, transaction ou écriture SQL cross-domain n’est créée.

## Décision

La persistence est certifiée sur PostgreSQL 18.x. La sérialisation des payloads
force leur forme objet JSON via `(object) $candidate->payload`, conformément à
la contrainte `jsonb_typeof(payload) = 'object'`.
