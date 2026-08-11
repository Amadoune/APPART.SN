# Identity Access HTTP Runtime 01

## Objectif audité

Matérialiser Login, Session Creation, Session Inspection, Rotation, Logout et le binding réel de `IdentityAccessHttpRuntime`, exclusivement par composition des capacités IAM existantes.

## Résultat de qualification

La frontière HTTP et les tables de persistance existent, mais les autorités applicatives nécessaires au Runtime ne sont pas matérialisées.

| Capacité requise | État réel |
|---|---|
| résolution identifier → AccountId sans énumération | MISSING |
| `CredentialVerifier` plaintext → verdict fermé | MISSING |
| politique de tentatives/lockout exécutable | MISSING |
| politique de création de session | MISSING |
| génération et hashing du secret de session | MISSING |
| politique idle/absolute expiration | MISSING |
| rotation de session | MISSING |
| révocation/logout owner-scoped | MISSING |
| stores PostgreSQL génériques | PRESENT |
| orchestration transactionnelle à callback | PRESENT |

## Incompatibilité démontrée

`Account::passwordMatches()` accepte un `PasswordHash` déjà encodé et effectue une égalité de hash. Il ne vérifie pas un credential en clair. Le Boundary Audit 5.1 interdit explicitement de construire un `PasswordHash` depuis le mot de passe reçu et exige un adapter spécialisé ne révélant jamais le hash.

Le Contract Blueprint 5.1 qualifie `CredentialVerifier`, `LoginIdentityResolver`, `AuthenticationAttemptStore` et `SessionStore` comme ports futurs. Les stores ont été matérialisés au niveau Persistence, mais les deux premières autorités et les policies applicatives ne l'ont pas été.

## Décision

Le Runtime ne peut pas être construit par composition mécanique. Il faudrait d'abord ouvrir un amendement IAM Contracts/Authentication & Session Policy. Le présent chantier n'autorise ni nouveaux contrats de sécurité ni nouvelles règles IAM.
