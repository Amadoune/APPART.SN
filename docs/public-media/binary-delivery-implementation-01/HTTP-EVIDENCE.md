# HTTP Evidence

Pour un résultat `Found`, l'adapter retourne :

- HTTP 200 ;
- corps binaire réel streamé ;
- `Content-Type` issu des métadonnées autoritatives ;
- `Content-Length` autoritatif ;
- `Cache-Control: no-store` (Symfony peut compléter par `private`) ;
- `X-Content-Type-Options: nosniff`.

Les indisponibilités publiques sont des réponses 404 vides et uniformes. Une panne de dépendance est réduite en 503 vide, conformément à la distinction certifiée.
