# Public Media Binary Delivery Implementation 01

## Résultat

Le chemin productif `GET /media/{mediaId}/revisions/{assetVersion}` est implémenté comme une livraison publique en lecture seule, sans session IAM et sans dépendance à Public Projection.

La composition est : route HTTP → contrôleur-adapter → `ResolvePublicMediaBinaryV1` → source owner-side Media → storage privé → flux binaire.

Les paramètres sont fermés à un UUID Media valide et une version entière strictement positive. Le resolver exige un média actif, un asset `ready` de version exacte, un attachment appliqué et une relation à un Listing publié. Aucun fallback vers une autre révision n'existe.

## Hors périmètre respecté

Aucune PublicMediaDecision, projection, recherche, génération, transformation binaire ou migration n'est créée. Aucun domaine tiers n'est écrit.
