# Phase 5.1I — Anti-enumeration & Rate Limiting

Login ne distingue publiquement jamais identité absente, credential invalide, lockout, Account indisponible ou vérification requise : `401 authentication_failed`.

Password Recovery retourne toujours `202 accepted` pour un résultat métier déterminé, que l'identité existe ou non. Seule une indisponibilité technique globale retourne `503`.

Trois limiters sont séparés :

- login : 5/minute par fingerprint HMAC `(identifiant normalisé, IP)` ;
- recovery : 3/minute par fingerprint HMAC ;
- endpoints authentifiés : 60/minute par hash du cookie.

Les clés de rate limit ne contiennent jamais l'identifiant clair, le cookie ou une PII directement exploitable.
