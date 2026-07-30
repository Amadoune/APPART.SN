# Phase 5.2B — Contracts Foundation

## Statut

**GO CERTIFIÉ — FERMÉ.**

Ce dossier définit les contrats normatifs sans créer d’interface PHP ni
d’implémentation.

## Owners

| Owner | Responsabilité exclusive |
|---|---|
| MediaUpload | réservation, réception, finalisation et expiration d’un upload |
| MediaAsset | identité canonique, preuve du contenu, sûreté et rétention |
| MediaProcessing | orchestration technique, leases, recettes et variantes |
| MediaQuota | limites, réservations, consommation et libération |

## Règles communes

- IDs UUID opaques, timestamps UTC et versions de contrat explicites ;
- chaque mutation porte un `intentId` UUID et un checksum canonique ;
- même intent/checksum retourne `AlreadyApplied` ; même intent/checksum
  différent retourne `DivergentIntent` ;
- résultats fermés, sans exception technique dans la surface publique ;
- concurrence sérialisée par owner et scope minimal ;
- diagnostics publics séparés des diagnostics internes ;
- aucun nom de fichier, chemin, URL signée, object key, secret, token, PII ou
  résultat antivirus dans les événements ;
- aucune dépendance à Laravel, PDO, SDK de stockage ou fournisseur de scan ;
- aucun owner ne lit ou écrit le store d’un autre owner.

## Interactions autorisées

MediaUpload réserve MediaQuota, puis remet un contenu finalisé à MediaAsset.
MediaAsset commande MediaProcessing. MediaProcessing retourne uniquement un
résultat fermé à MediaAsset. Un Asset Ready peut être remis à la frontière F-06
certifiée par l’amendement, sans transaction ACID cross-domain.

## Gates

Contracts Foundation ne vaut pas autorisation d’implémenter. L’implémentation
reste bloquée tant que `A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01` n’est pas GO
CERTIFIÉ et FERMÉ.
