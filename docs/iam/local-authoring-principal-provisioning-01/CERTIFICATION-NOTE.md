# Note de certification

Un nouveau principal IAM strictement local a été créé par la chaîne certifiée CredentialHashAuthorityV1 → RegisterAccount → AccountRegistry, sans GrantRole et sans modification d’un compte existant.

Le Login HTTPS réel a émis une Session IAM reconnue par `RequireIdentityAccessSession`. Le principal correct accède à `/authoring/workspace` sans rôle ni capability supplémentaire. Le cookie applicatif conserve les attributs `Secure`, `HttpOnly` et `SameSite=Strict`.

Le principal reviewer reste isolé. Aucun Listing n’a été créé et RC2 Iteration 11 n’a pas été reprise dans ce chantier.

Les fichiers temporaires opérateur ont été supprimés. Le credential clair est absent du repository et de cette documentation. `git diff --check` est PASS.

## Verdict

**GO PROPOSÉ**

APPART.TEST LOCAL AUTHORING PRINCIPAL PROVISIONING 01
