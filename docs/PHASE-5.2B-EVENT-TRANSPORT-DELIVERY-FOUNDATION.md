# Phase 5.2B — Event / Transport / Routing / Delivery Foundation

## Statut

**GO CERTIFIÉE — FERMÉE.**

## Catalogue V1

Le catalogue est fermé à :

- `media.ingestion.asset.ready.v1` ;
- `media.ingestion.asset.rejected.v1` ;
- `media.ingestion.asset.purged.v1`.

L’unique Event Owner est `MediaIngestion.Asset`.

## Transport

`MediaIngestionDeliveryMessageV1` porte une enveloppe canonique V1, un eventId,
un messageId et deux checksums déterministes. Le serializer reconstruit
l’événement et refuse toute altération ou forme non canonique.

Les payloads excluent PII, filename, object key, URL, binaire, token et
diagnostic de scan.

## Routing

- Ready : audit privé, statut Authoring et handoff Media Attachment ;
- Rejected : audit privé et statut Authoring ;
- Purged : audit privé et reconciliation.

Le consumer revalide le transport et refuse toute destination hors matrice.

## Delivery

Les outcomes sont fermés. Un échec transitoire est rejoué au maximum cinq
tentatives ; échec permanent, divergence ou épuisement vont en quarantaine.
L’observation technique contient seulement messageId, destination, outcome et
numéro de tentative.

## Frontières

Aucune migration, persistence, Outbox, HTTP ou orchestration inter-owner n’est
ajoutée. Runtime et migrations 058–059 sont inchangés. Runtime Health reste à
58 capacités.
