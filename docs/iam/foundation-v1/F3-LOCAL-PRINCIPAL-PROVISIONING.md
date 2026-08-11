# F3 — Local Principal Provisioning & HTTPS Session Proof

## Provisioning local

Un principal local réel a été créé par la chaîne certifiée :

`CredentialHashAuthorityV1 → PasswordHash Argon2id → RegisterAccount → AccountRegistry`

Identité locale :

- email : `f3.local@appart.test` ;
- téléphone : E.164 local de démonstration ;
- AccountId : `019feb91-e37e-7093-921d-22c90063aa72` ;
- environnement : `local` uniquement.

Aucun SQL, hash manuel, Seeder, cookie injecté ou session artificielle n'a été utilisé. Le principal est durablement relu par `LoginIdentityResolverV1`.

## Frontière navigateur observée

`https://appart.test` répond et présente les actions « Se connecter » et « Déposer une annonce », mais ces actions restent informatives : aucun formulaire Login, script Login ou navigation IAM n'est branché.

La route `POST /api/identity-access/login` requiert simultanément :

- un payload identifier/credential/requestedAt ;
- l'en-tête `Idempotency-Key` ;
- la protection CSRF du groupe Web.

La Home publique n'expose aucun `meta[name=csrf-token]` ni formulaire `@csrf`. Le seul token CSRF observé appartient à `/authoring/workspace`, qui est lui-même protégé par `RequireIdentityAccessSession`. Le bootstrap navigateur est donc circulaire.

## Cause racine unique

**Aucune surface Web publique certifiée ne transporte CSRF + Idempotency-Key vers le Login IAM.**

La corriger ouvrirait précisément IAM Web Entry, explicitement interdit dans F3. F3 ne modifie donc aucune surface technique.
