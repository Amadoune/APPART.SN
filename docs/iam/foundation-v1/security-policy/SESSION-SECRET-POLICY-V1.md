# Session Secret Policy V1

## Secret présenté

- génération : `random_bytes(32)` ;
- entropie : 256 bits ;
- encodage : base64url RFC 4648 §5 sans `=` ;
- longueur de la partie secrète : 43 caractères ASCII ;
- format opaque cookie : `s1.<session-id-uuid>.<secret-base64url>` ;
- le SessionId n'est pas un secret et sert uniquement au lookup owner-scoped.

Tout échec CSPRNG produit `DependencyUnavailable`; aucun fallback pseudo-aléatoire n'est admis.

## Représentation persistée

- version : `session-secret-hmac-sha256-v1` ;
- digest : HMAC-SHA-256 du secret binaire avec une clé serveur de 256 bits minimum ;
- format stocké : `session-secret-hmac-sha256-v1.<key-id>.<digest-hex-64>` ;
- clé conservée hors base dans l'autorité de secrets ;
- comparaison : `hash_equals(digestConnu, digestPrésenté)` avec longueurs validées ;
- aucune valeur claire dans persistence, résultat, événement, log ou diagnostic.

La clé active émet les nouvelles sessions. Les clés antérieures restent disponibles uniquement pour vérifier les sessions non expirées déjà émises. Une clé inconnue ou indisponible invalide la session fail-closed. Une rotation de clé n'allonge jamais la durée d'une session.

## Après rotation

La rotation crée un nouveau SessionId et un nouveau secret indépendant. L'ancienne row devient `Rotated` dans la même transaction avant émission du nouveau cookie ; aucune période de grâce n'est admise.

Références : [PHP `random_bytes`](https://www.php.net/manual/en/function.random-bytes.php), [PHP `hash_hmac`](https://www.php.net/manual/en/function.hash-hmac.php), [PHP `hash_equals`](https://www.php.net/manual/en/function.hash-equals.php), [RFC 4648](https://www.rfc-editor.org/rfc/rfc4648.html#section-5).
