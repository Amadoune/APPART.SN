# F1 — Authentication & Session Authority Implementation

## Périmètre exécuté

F1 matérialise exclusivement les six autorités internes décidées par `IAM SECURITY POLICY AUTHORITY v1` :

- `LoginIdentityResolverV1` normalise email et téléphone E.164, puis résout un `AccountId` via un port IAM interne ;
- `CredentialHashAuthorityV1` produit exclusivement Argon2id (`memory_cost=19456`, `time_cost=2`, `threads=1`) sous `credential-hash-v1` ;
- `CredentialVerifierV1` vérifie le plaintext avec `password_verify` et ne propose un rehash qu'après succès ;
- `SessionSecretAuthorityV1` génère 32 octets aléatoires, encode en base64url et persiste uniquement une preuve HMAC-SHA-256 versionnée ;
- `SessionPolicyEvaluatorV1` applique idle 30 minutes, absolu 8 heures, rotation 30 minutes et concurrence maximale de cinq sessions actives ;
- `SessionReductionV1` réduit mécaniquement snapshot, preuve de secret et policy vers un verdict fermé.

## Frontière

L'Application ne contient aucun SQL et ne dépend ni de Laravel ni de HTTP. `PostgreSqlAuthenticationAuthoritySource` est l'unique adaptateur de lecture des identités et credentials historiques. Les écritures Session continuent d'utiliser `PostgreSqlSessionStore`, ses advisory locks transactionnels, son optimistic locking et son replay déterministe.

Le binding `IdentityAccessHttpRuntime → FailClosedIdentityAccessHttpRuntime` demeure strictement inchangé. F2 n'est pas ouverte.

## Persistence additive

La migration 095 ajoute à `identity_access_completion.sessions` : `policy_version`, `original_issued_at`, `idle_expires_at` et `absolute_expires_at`, plus un index owner-scoped pour l'ordre déterministe des sessions actives. Les lignes historiques restent nulles et sont donc réduites fail-closed. Le rollback 095 supprime exclusivement ces ajouts.

## Confidentialité

Les secrets et hashes sont marqués sensibles, expurgés du debug et ne sont jamais sérialisés par les résultats publics. Une identité absente ou invalide est ramenée au même statut interne `NotResolved`; aucune différence HTTP n'est introduite à ce jalon.
