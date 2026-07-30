# Phase 5.2B — Media Ingestion Final Freeze Specification

## Activation

Le gel est actif et exécutoire depuis la décision d’autorité **GO FINAL**.

## Périmètre fonctionnel proposé au gel

- `MediaUpload` ;
- `MediaAsset` ;
- `MediaProcessing` ;
- `MediaQuota` ;
- `AttachReadyMediaAssetV1` ;
- Persistence Foundation ;
- Runtime Foundation ;
- catalogue Event V1 :
  - `media.ingestion.asset.ready.v1` ;
  - `media.ingestion.asset.rejected.v1` ;
  - `media.ingestion.asset.purged.v1` ;
- transport, routing, delivery, retry, replay et quarantaine ;
- Atomic Delivery et Outbox propriétaire ;
- résultats fermés, checksums, idempotence, rollback, concurrence et leases.

## Migrations proposées au gel

| Migration | Statut |
|---|---|
| 058 — Media Ingestion | certifiée, additive, gelée |
| 059 — Media Attachment intents | certifiée, additive, gelée |
| 060 — Media Ingestion Event Outbox | certifiée, additive, gelée |

La migration 060 fixe contractuellement `message_id` à `varchar(83)`, soit le
préfixe `media-ingestion-v1-` de 19 caractères et un SHA-256 hexadécimal de
64 caractères. Les migrations 058–060 ne doivent plus être modifiées après le
GO FINAL ; toute évolution devra être additive ou précédée d’un amendement
versionné.

## Frontières protégées

Le gel n’accorde aucune autorité sur :

- F-06 `MediaCollection` et `MediaItem` ;
- F-11 Public Listing Projection ;
- F-15 Delivery Foundation historique ;
- F-17/F-18 Identity & Access ;
- F-19/F-20 Property & Listing Authoring ;
- Reservation Lifecycle.

Il interdit toute FK cross-domain, cascade, écriture SQL dans une capacité
gelée ou transaction ACID cross-domain.

## Règle post-gel

Après GO FINAL, toute évolution de Media Ingestion, de ses contrats, événements,
Runtime, Outbox ou migrations 058–060 exigera un amendement versionné préalable.
Les gels sont enregistrés sous F-21 Media Ingestion et F-22 Migrations Media
Ingestion 058–060.
