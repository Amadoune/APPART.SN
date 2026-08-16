# Preuve Login et Session

Le formulaire réel `https://appart.test/connexion?next=%2Fauthoring%2Fworkspace` a été utilisé avec le principal local nouvellement créé.

Résultat observé :

- Login IAM accepté ;
- navigation réelle vers `https://appart.test/authoring/workspace` ;
- workspace rendu avec l’étape « Votre projet » ;
- bouton Logout présent ;
- aucune erreur ou warning console ;
- une session active relue via `IdentityAccessSessionStore` pour l’AccountId `a2110000-0000-4000-8000-000000000001`.

Le cookie est émis exclusivement par `IdentityAccessHttpController` après `IdentityAccessHttpStatus::Succeeded`. Sa configuration effective est `__Host-appart_session`, Path `/`, Domain absent, `Secure`, `HttpOnly`, `SameSite=Strict`. Son acceptation est démontrée par l’accès authentifié au workspace.

Aucun cookie, secret de session ou AccountId n’a été injecté.
