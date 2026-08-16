# MIME and content type

Le delivery reprend le `contentType` autoritatif de l'asset ready après validation contre le catalogue Media accepté. RC2 porte `image/jpeg`.

Réponse : `Content-Type` validé, `Content-Disposition: inline`, `X-Content-Type-Options: nosniff`, longueur si connue. Aucun filename original n'est requis ni exposé.
