# Phase 5.2B — HTTP Boundary Discovery

## Surface candidate

- réserver une session d’upload ;
- obtenir une cible d’upload bornée et opaque ;
- finaliser l’upload ;
- consulter un statut public minimal ;
- abandonner une session non finalisée ;
- demander le rattachement d’un asset prêt via la future frontière F-06.

## Sécurité obligatoire

- session IAM et auto-scope exclusifs ;
- autorisation owner/délégation issue de F-19 ;
- CSRF pour le parcours navigateur ;
- `Idempotency-Key` UUID pour chaque mutation ;
- validation stricte et refus des champs inconnus ;
- limite de taille appliquée au flux réellement lu ;
- détection MIME par signature et décodage complet ;
- noms originaux non utilisés comme chemin ou object key ;
- URLs signées courtes, audience et méthode bornées ;
- rate limiting HMAC sans PII ;
- réponses `no-store`, `nosniff` et sans diagnostics internes ;
- protection contre decompression bombs, polyglots, path traversal et SSRF.

## Interdictions

Le serveur public ne sert jamais directement la quarantaine, ne révèle aucun
chemin de stockage et n’accepte aucune URL distante à importer dans le MVP.
