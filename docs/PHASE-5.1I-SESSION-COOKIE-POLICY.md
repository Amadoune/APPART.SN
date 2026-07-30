# Phase 5.1I — Session Cookie Policy

Cookie V1 :

- nom `__Host-appart_session` ;
- `Secure` ;
- `HttpOnly` ;
- `SameSite=Strict` ;
- path `/` ;
- aucun domain ;
- expiration fournie par la décision Session ;
- secret absent du body, des événements et des logs.

Logout efface explicitement le cookie. Toutes les réponses IAM portent `Cache-Control: no-store`, `Pragma: no-cache` et `X-Content-Type-Options: nosniff`.

Le middleware inspecte le secret au travers du port Runtime. Toute absence, invalidité, indisponibilité ou exception produit la même réponse `401 authentication_required`.
