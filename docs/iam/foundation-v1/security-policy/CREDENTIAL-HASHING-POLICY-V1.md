# Credential Hashing Policy V1

## Décision APPART.SN

- policy : `credential-hash-v1` ;
- primitive : `PASSWORD_ARGON2ID` ;
- `memory_cost` : 19 456 KiB ;
- `time_cost` : 2 ;
- `threads` : 1 ;
- sel : aléatoire et généré automatiquement par `password_hash` ;
- stockage : chaîne PHC Argon2id complète dans la limite historique de 255 caractères ;
- vérification : `password_verify(plaintext, encodedHash)` ;
- comparaison de deux hashes indépendants : interdite.

Cette configuration adopte le minimum Argon2id actuel recommandé par OWASP et le fige sous une version APPART.SN, au lieu de dépendre des defaults PHP évolutifs. Le runtime PHP doit annoncer `argon2id` dans `password_algos()` ; sinon l'autorité est indisponible et refuse toute création/vérification.

## Rehash

Après vérification réussie uniquement, `password_needs_rehash` compare le hash aux paramètres V1. Un rehash nécessaire est écrit par l'owner Account avec optimistic locking dans la transaction d'authentification ; un conflit ne crée pas de session et peut être rejoué.

## Hashes historiques

- Argon2id compatible V1 : vérification normale ;
- Argon2id avec autres paramètres ou bcrypt PHP `$2y$` recevable : vérification par `password_verify`, puis rehash V1 obligatoire avant création de Session ;
- format inconnu, hash illisible ou primitive non prise en charge : fail-closed `AccountUnavailable` interne, réduit publiquement comme credentials invalides ;
- aucun MD5, SHA-1, SHA-256 rapide ou algorithme maison ;
- aucun backfill sans plaintext et aucune réinitialisation implicite.

Références : [PHP password hashing](https://www.php.net/manual/en/ref.password.php), [PHP `password_needs_rehash`](https://www.php.net/manual/en/function.password-needs-rehash.php), [OWASP Password Storage](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html).
