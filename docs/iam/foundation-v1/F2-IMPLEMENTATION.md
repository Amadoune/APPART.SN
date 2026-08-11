# F2 — HTTP Runtime Composition Implementation

## Runtime réel

`DeterministicIdentityAccessHttpRuntime` implémente le contrat HTTP existant et compose exclusivement :

- `LoginIdentityResolverV1` ;
- `CredentialVerifierV1` ;
- `SessionSecretAuthorityV1` ;
- `SessionPolicyEvaluatorV1` ;
- `SessionReductionV1` ;
- `IdentityAccessSessionStore` ;
- `IdentityAccessOrchestrator`.

Le Runtime ne contient aucun SQL, appel cryptographique, dépendance Laravel/Symfony ou lecture directe de requête.

## Login

La résolution email/téléphone et la vérification credential restent portées par F1. Une authentification valide crée une Session owner-scoped dans l'orchestrateur atomique. Le cookie retourné respecte `s1.<SessionId>.<secret-base64url>`. La preuve HMAC seule est persistée.

Lors de l'admission d'une sixième session, la policy F1 sélectionne la plus ancienne selon `originalIssuedAt`, puis `SessionId`; sa révocation et la création sont exécutées dans la transaction IAM owner-scoped.

Compte absent et credential incorrect produisent le même résultat fermé `GenericFailure` et la même réponse publique.

## Inspection et contexte authentifié

`inspectSession()` parse l'enveloppe opaque, relit la Session par `SessionId`, appelle `SessionReductionV1`, puis produit exclusivement `AuthenticatedSessionContext(AccountId, SessionId)`.

Le middleware transporte cet objet typé et le Controller le place mécaniquement dans `IdentityAccessHttpCommand`. Aucun secret, cookie, HMAC, timestamp, état ou objet HTTP n'entre dans le contexte.

## Rotation et Logout

Le Runtime relit obligatoirement la Session, vérifie la concordance `AccountId + SessionId`, puis utilise l'orchestrateur et l'optimistic locking :

- Rotation marque l'ancienne row `Rotated`, génère une nouvelle identité et un nouveau secret, conserve `originalIssuedAt` et l'échéance absolue ;
- Logout marque exactement la Session courante `Revoked` ;
- un replay compatible de Logout est idempotent ;
- l'ancien cookie est immédiatement refusé après Rotation ou Logout.

## Compatibilité

Routes, formats JSON, politique cookie Secure/HttpOnly/SameSite Strict, HTTPS, Controllers publics et capacités Property, Media, Search et Projection conservent leur comportement. Recovery, Profile, Contact et Closure restent explicitement fail-closed dans le Runtime F2.

Le binding final est `IdentityAccessHttpRuntime → DeterministicIdentityAccessHttpRuntime`, singleton et lazy.
