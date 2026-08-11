# IAM Web Entry Experience

## Périmètre

Ce sprint matérialise uniquement l’entrée Web vers les capacités IAM certifiées. Il ajoute la route publique `GET /connexion`, une vue responsive, et une composition navigateur vers les routes IAM existantes.

## Parcours

`Accueil → Connexion → POST /api/identity-access/login → Workspace → POST /api/identity-access/logout → Connexion`

Le formulaire transporte mécaniquement `identifier`, `credential`, le jeton CSRF du document, un `Idempotency-Key` UUID propre à chaque tentative et `requestedAt`. L’ownership et les décisions de sécurité restent dans IAM.

## Frontières préservées

- Aucun changement de Foundation IAM, Runtime IAM, cookie, HTTPS ou politique de sécurité.
- Aucun SQL, repository ou décision métier dans la couche Web.
- Aucune surface Recovery, Register ou Profile.
- La destination post-login est limitée à un chemin local ; la valeur par défaut est `/authoring/workspace`.

## Expérience

La page expose un H1 unique, des labels explicites, les attributs d’autocomplétion, un focus visible, un état de chargement, une erreur générique et un succès annoncé via `aria-live`.
