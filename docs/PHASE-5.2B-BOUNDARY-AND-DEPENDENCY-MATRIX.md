# Phase 5.2B — Boundary & Dependency Matrix

| Source | Cible | Autorisé | Forme |
|---|---|---:|---|
| MediaUpload | MediaQuota | oui | port Application public |
| MediaUpload | MediaAsset | oui | handoff fermé après finalisation |
| MediaAsset | MediaProcessing | oui | commande idempotente |
| MediaProcessing | MediaAsset | oui | résultat de traitement owner-scoped, sans cycle de type |
| Media Ingestion | F-17 IAM | lecture | disponibilité/session via contrat public |
| Media Ingestion | F-19 Authoring | lecture | owner/délégation et portée via contrat public |
| Media Ingestion | F-06 Media | conditionnel | futur contrat public versionné après amendement |
| Media Ingestion | F-11 Projection | non | aucune écriture ni publication directe |
| Media Ingestion | F-15 Delivery/Outbox | non au Discovery | future intégration seulement dans un jalon dédié |

## Dépendances interdites

- SQL, FK ou transaction ACID cross-domain ;
- appel direct à `AddMedia`, `CreateMediaCollection` ou
  `MediaCollectionRegistry` depuis 5.2B ;
- dépendance Application vers Infrastructure, Laravel, stockage ou SDK cloud ;
- exposition d’un object key, chemin local ou diagnostic de scan ;
- activation d’un MediaItem avant sûreté et complétude de l’asset ;
- couplage du quota à une table Account, Property ou Listing.

## Gate

La frontière Media Attachment a été ajoutée par
`A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01`, GO CERTIFIÉ et FERMÉ. Aucun amendement
5.2B n’est ouvert.
