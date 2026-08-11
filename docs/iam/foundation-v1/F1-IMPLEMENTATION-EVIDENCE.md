# F1 — Implementation Evidence

## Autorités

| Autorité | Implémentation | Preuve |
|---|---|---|
| LoginIdentityResolverV1 | `DeterministicLoginIdentityResolver` | email normalisé, téléphone E.164, AccountId non accepté comme login |
| CredentialHashAuthorityV1 | `Argon2IdCredentialHashAuthority` | Argon2id 19456/2/1, version `credential-hash-v1` |
| CredentialVerifierV1 | `DeterministicCredentialVerifier` | succès, rejet, account unavailable, dependency unavailable, rehash après succès seulement |
| SessionSecretAuthorityV1 | `HmacSha256SessionSecretAuthority` | 32 octets, base64url 43 caractères, HMAC versionné, `hash_equals` |
| SessionPolicyEvaluatorV1 | `DeterministicSessionPolicyEvaluator` | idle 30 min, absolu 8 h, rotation 30 min, policy inconnue fail-closed |
| SessionReductionV1 | `DeterministicSessionReduction` | réduction exhaustive sans HTTP ni décision métier |

## Concurrence et replay

Le store Session existant conserve son advisory lock transactionnel, l'optimistic locking et les statuts `Applied`, `IdempotentReplay`, `VersionConflict`. La policy trie les sessions par `originalIssuedAt`, puis `sessionId`, et désigne exclusivement la plus ancienne lors de l'admission d'une sixième session. Le test PostgreSQL prouve l'application, le replay identique et le conflit de version.

## Intégrité de frontière

- aucune modification de `IdentityAccessHttpRuntime` ;
- binding fail-closed inchangé ;
- aucun Controller, Request, cookie ou middleware modifié ;
- aucun SQL dans Application ;
- aucune dépendance Property, Media, Search ou Projection ;
- migration 095 additive et rollback dédié.
