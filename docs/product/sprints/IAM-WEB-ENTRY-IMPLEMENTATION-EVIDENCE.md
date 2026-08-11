# IAM Web Entry — Implementation Evidence

## Livrables techniques

- `GET /connexion` et lien réel depuis le header public.
- `resources/views/iam-login.blade.php` : formulaire Login uniquement.
- `resources/js/iam-web-entry.js` : CSRF, idempotence, appel du Runtime HTTP existant, redirection et logout.
- `resources/views/authoring-workspace.blade.php` : contrôle Logout réel.
- `tests/Feature/IamWebEntryExperienceTest.php` : entrée publique, formulaire minimal et fail-closed.

## Composition certifiée

Le POST Login et le POST Logout restent servis par `IdentityAccessHttpController`. Le script ne reçoit jamais le secret de session : le navigateur accepte exclusivement le cookie `__Host-appart_session` Secure/HttpOnly/SameSite=Strict produit par le Controller certifié.

## Preuve locale

- Principal local certifié F3 utilisé : `f3.local@appart.test`.
- Login HTTPS : `succeeded` et navigation vers `/authoring/workspace`.
- Reload du workspace : session toujours valide.
- Logout : retour `/connexion?logged_out=1`.
- Accès ultérieur au workspace : HTTP 401 `authentication_required`.
- Capture : `docs/product/sprints/IAM-WEB-ENTRY-EXPERIENCE.png`.
