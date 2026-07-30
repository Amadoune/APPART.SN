# Phase 5.2B — Ownership Matrix

| Autorité | Aggregate/State Owner | Persistence Owner | Event Owner futur | HTTP Owner futur |
|---|---|---|---|---|
| MediaUpload | `MediaIngestion.Upload` | schéma owner-scoped Upload | Media Ingestion | adaptateur Media Ingestion |
| MediaAsset | `MediaIngestion.Asset` | métadonnées Asset + namespace objet privé | Media Ingestion | aucun accès binaire direct |
| MediaProcessing | `MediaIngestion.Processing` | jobs, attempts et variantes | Media Ingestion | lecture de progression seulement |
| MediaQuota | `MediaIngestion.Quota` | ledger et réservations Quota | Media Ingestion | commande via façade Ingestion |
| MediaCollection/MediaItem | F-06 Media | F-06 Media | F-06 | F-06 |
| Property/Listing ownership | F-19 Authoring | F-19 | F-19 | F-19 |
| Session/Account availability | F-17 IAM | F-17 | F-17 | F-17 |

## Règles

- un state et une table n’ont qu’un owner ;
- MediaAsset possède le binaire technique, jamais sa place dans une galerie ;
- F-06 possède l’association Collection/Item, l’ordre, le primaire et les
  états Active/Removed/Archived ;
- MediaProcessing ne décide ni publication ni modération ;
- MediaQuota ne lit ni n’écrit directement les stores IAM ou Authoring.
