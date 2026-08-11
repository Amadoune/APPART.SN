# IAM Security Policy Authority V1

## Autorité normative

Ce dossier constitue la décision exécutable APPART.SN pour F1 Authentication & Session Authority. Les recommandations externes éclairent les options ; les valeurs ci-dessous sont des décisions APPART.SN.

## Catalogue V1

| Domaine | Décision APPART.SN V1 |
|---|---|
| login identifiers | email ou téléphone E.164 |
| credential | Argon2id, 19 456 KiB, 2 passes, parallélisme 1 |
| format credential | format PHC produit par `PASSWORD_ARGON2ID`, policy `credential-hash-v1` |
| secret Session | 32 octets CSPRNG, base64url sans padding, 256 bits |
| preuve persistée Session | HMAC-SHA-256 avec clé serveur versionnée, jamais le secret |
| idle timeout | 30 minutes depuis la dernière activité acceptée |
| absolute lifetime | 8 heures depuis l'authentification initiale |
| rotation temporelle | au plus 30 minutes par secret |
| concurrence | 5 sessions actives maximum par Account |
| policy | `session-policy-v1` |

## Références externes

- [PHP `password_hash`](https://www.php.net/manual/en/function.password-hash.php) : Argon2id, sel automatique et paramètres explicites.
- [OWASP Password Storage](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html) : minimum Argon2id 19 MiB, 2 itérations, parallélisme 1.
- [PHP `random_bytes`](https://www.php.net/manual/en/function.random-bytes.php) : source cryptographiquement sûre.
- [RFC 4648 §5](https://www.rfc-editor.org/rfc/rfc4648.html#section-5) : encodage base64url.
- [OWASP Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html) : expirations serveur, renouvellement et rotation après changement de privilège.

## Applicabilité

La même policy s'applique en local, test et production. Une primitive indisponible, une clé HMAC absente ou une policy inconnue produit un refus fail-closed.
