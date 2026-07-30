# Phase 5.2B — Persistence Migration Plan

## Migration 058 — Media Ingestion

Crée le schéma propriétaire `media_ingestion`, les tables `uploads`, `assets`,
`processing`, `quotas` et leurs quatre journaux d’intents. Chaque state possède
un UUID, un état fermé, une version, le dernier intent/checksum, un payload
JSONB objet et un timestamp.

La migration down supprime exclusivement le nouveau schéma.

## Migration 059 — Media Attachment intents

Ajoute `media.media_attachment_intents` conformément à l’amendement certifié.
Le journal est limité à `AttachReadyMediaAssetV1` et conserve intent, checksum,
collectionId, propertyId, mediaId, résultat et version appliquée.

La migration down supprime exclusivement cette table.

## Garanties

- ordre strictement postérieur à 057 ;
- aucun `ALTER TABLE` d’une structure existante ;
- aucune FK et aucune cascade ;
- aucune réécriture de migration certifiée ;
- rollback local et déterministe.
