# Decision Matrix

| Autorité | Valeur V1 | Source APPART.SN | Éclairage externe | Compatibilité |
|---|---|---|---|---|
| identifiers | email + téléphone E.164 | VO existants | aucune valeur externe imposée | additive resolver |
| normalisation email | trim + lowercase + validation 254 | `EmailAddress` | standards intégrés PHP | inchangée |
| normalisation téléphone | séparateurs retirés, `+` + 8–15 chiffres | `PhoneNumber` | format international existant | inchangée |
| credential | Argon2id 19 456/2/1 | décision V1 | OWASP + PHP | colonne 255 compatible |
| rehash | après succès, avant Session | invariant Account owner | PHP `password_needs_rehash` | bcrypt PHP recevable |
| secret | 32 octets CSPRNG, base64url 43 | décision V1 | PHP + RFC 4648 | nouveau format opaque |
| preuve secret | HMAC-SHA-256 versionné | décision V1 | PHP HMAC/hash_equals | `secret_hash` compatible |
| idle | 30 minutes | décision V1 | plage OWASP 15–30 | colonnes additives |
| absolute | 8 heures | décision V1 | plage OWASP 4–8 | colonnes additives |
| rotation | auth, 30 min, privilège, fresh auth | décision V1 | OWASP | atomicité existante réutilisée |
| concurrence | 5 sessions | décision APPART.SN | compromis produit-sécurité | verrou Account requis |
| dépassement | révoquer la plus ancienne | décision APPART.SN | aucune | déterministe |
| policy | `session-policy-v1` | décision V1 | versioning interne | inconnue = fail-closed |

Toutes les autorités requises par F1 disposent d'une valeur normative. Aucune valeur résiduelle n'est laissée à l'implémentation.
