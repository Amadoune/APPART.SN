# F2 — HTTP Runtime Composition

## Statut

`NOT_IMPLEMENTED — FAIL-CLOSED`

## Frontière auditée

La composition Login est techniquement dérivable des autorités F1 : résolution de l'identité, vérification du credential, émission d'un secret, écriture du snapshot Session et réduction fermée.

La composition complète exigée par F2 ne l'est pas avec le contrat HTTP actuel :

1. `RequireIdentityAccessSession` transmet le cookie à `inspectSession()` ;
2. après validation, le middleware place uniquement `iam_account_id` dans la requête ;
3. `IdentityAccessHttpController` construit ensuite `IdentityAccessHttpCommand` avec cet `AccountId`, mais sans identifiant de session, secret présenté ou handle opaque ;
4. `Logout` et `RenewSession` ne possèdent ainsi aucune identité Session exploitable par le Runtime.

La Security Policy autorise jusqu'à cinq sessions actives par compte. L'`AccountId` seul ne permet donc pas de choisir mécaniquement la session courante. Révoquer toutes les sessions ou choisir arbitrairement une ligne introduirait une nouvelle décision de sécurité, interdite par F2.

## Cause racine unique

**Le contexte de session authentifié est perdu entre `inspectSession()` et `execute()`** : la frontière HTTP conserve l'identité du compte, mais pas l'identité de la session validée.

Ce défaut bloque simultanément Logout et Rotation. Il empêche donc le remplacement terminal du binding fail-closed.

## État conservé

- `IdentityAccessHttpRuntime` reste lié à `FailClosedIdentityAccessHttpRuntime` ;
- aucun Controller, Request, Middleware, cookie, route ou Provider n'est modifié ;
- aucune autorité F1 ni politique de sécurité n'est modifiée ;
- aucune persistence ou migration n'est ajoutée par F2.
