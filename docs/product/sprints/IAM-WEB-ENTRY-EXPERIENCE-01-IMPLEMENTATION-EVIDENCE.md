# IAM Web Entry Experience 01 — Implementation Evidence

## Statut

`NOT_IMPLEMENTED`

Le contrôle fail-closed a arrêté l'implémentation avant toute modification technique. Le parcours demandé ne peut pas être certifié sur le transport local actuel.

## Surfaces réutilisables confirmées

- `IdentityAccessHttpController` et `IdentityAccessHttpRuntime` existent.
- `POST /api/identity-access/login` accepte le contrat strict `identifier`, `credential`, `requestedAt` et l'en-tête `Idempotency-Key`.
- `RequireIdentityAccessSession` inspecte exclusivement le cookie de session certifié et injecte l'`AccountId` owner-scoped.
- `GET /authoring/workspace` existe et exige cette session.
- les APIs Property Authoring et Media Authoring sont déjà protégées par la même frontière IAM.

## Preuve visuelle

La capture `IAM-WEB-ENTRY-EXPERIENCE-01.png` consigne l'état initial : les actions « Se connecter » et « Déposer une annonce » sont encore des boutons informatifs et ne constituent pas une navigation réelle.

## Modifications techniques

Aucune route, vue, classe PHP, JavaScript, CSS, Provider, binding, Runtime, test ou migration n'a été créé ou modifié par ce sprint.
