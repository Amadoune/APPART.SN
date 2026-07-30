# Historical Account — Aggregate Mapping Inventory

## Racine Account

| Donnée | Type domaine | Cardinalité | Accès actuel | Reconstitution | Persistance requise |
|---|---|---:|---|---|---|
| id | `AccountId` | 1 | `id()` | oui | UUID, PK |
| email | `EmailAddress` | 1 | `email()` | oui | valeur normalisée, unique |
| phone | `PhoneNumber` | 1 | `phone()` | oui | valeur normalisée, unique |
| name | `PersonName` | 1 | `name()` | oui | valeur validée |
| lastChangedAt | `DateTimeImmutable` | 1 | non exposé | oui | timestamptz UTC |
| suspended historique | bool | 1 | `isSuspended()` | oui | bool |
| version globale | int | 1 | `version()` | oui | bigint >= 0 |
| recordedEvents | liste transitoire | 0..n | `releaseEvents()` destructif | non | jamais dans le snapshot |

État initial : `suspended=false`, `version=0`, `lastChangedAt=registeredAt`.
`Account::register()` émet `AccountRegistered` version 0; la persistance ne
rejoue pas cet événement.

## Credential

| Donnée | Accès actuel | Reconstitution | Persistance |
|---|---|---|---|
| passwordHash encodé | aucun export; sérialisation interdite | constructeur | secret chiffré au repos selon politique plateforme |
| changedAt | `changedAt()` | constructeur | timestamptz UTC |

Le hash est nécessaire à `passwordMatches()` et aux futurs changements. Une
simple empreinte du hash ne permettrait pas la reconstitution.

## Verification

Deux entrées obligatoires, exactement `email` et `phone`.

| Donnée | Accès actuel | Reconstitution | Persistance |
|---|---|---|---|
| channel | public readonly | constructeur | enum/check |
| token secret | aucun export | constructeur | secret protégé |
| issuedAt | aucun export | constructeur | timestamptz |
| expiresAt | aucun export | constructeur | timestamptz |
| verifiedAt | `verifiedAt()` | propriété interne non paramétrable | timestamptz nullable |

Un point supplémentaire existe : le constructeur ne permet pas de restaurer
directement `verifiedAt`. Appeler `verify()` pendant l'hydratation serait une
mutation métier et exigerait le token en clair. 4.9P-B doit prévoir une factory
de reconstitution non événementielle pour `Verification`.

## RoleAssignment

| Donnée | Accès actuel | Reconstitution | Persistance |
|---|---|---|---|
| roleId | public readonly | constructeur | texte validé |
| grantedAt | public readonly | constructeur | timestamptz |
| revokedAt | `revokedAt()` | non paramétrable au constructeur | timestamptz nullable |

L'historique complet est requis : plusieurs attributions successives d'un même
rôle sont possibles. Appeler `revoke()` pendant l'hydratation serait une
mutation; une factory de reconstitution non événementielle est requise.

## Consent

| Donnée | Accès actuel | Reconstitution | Persistance |
|---|---|---|---|
| purpose | public readonly | constructeur | texte validé |
| grantedAt | public readonly | constructeur | timestamptz |
| withdrawnAt | aucun getter | non paramétrable au constructeur | timestamptz nullable |

Plusieurs cycles accord/retrait d'un même purpose sont possibles. Une factory
de reconstitution non événementielle et un export borné sont requis.

## Événements historiques

| Événement | Déclencheur historique | Données supplémentaires |
|---|---|---|
| `AccountRegistered` | register | aucune |
| `EmailVerified` | verifyEmail | aucune |
| `PhoneVerified` | verifyPhone | aucune |
| `PasswordChanged` | changePassword | aucune |
| `AccountSuspended` | suspend historique | aucune |
| `AccountReactivated` | reactivate historique | aucune |
| `RoleGranted` | grantRole | roleId |
| `RoleRevoked` | revokeRole ou suspend historique | roleId |
| `ConsentGranted` | grantConsent | purpose |
| `ConsentWithdrawn` | withdrawConsent | purpose |
| `VerificationReplaced` | remplacement challenge | channel |

Tous portent `accountId`, `occurredAt`, `aggregateVersion`, `eventIndex`. Ils
restent transitoires tant qu'aucun contrat Event/Outbox n'est certifié.

## Mutations publiques et version

Chaque mutation réussie appelle une fois `changedAt()` et incrémente la version
globale d'une unité. `suspend()` peut révoquer plusieurs rôles et enregistrer
plusieurs événements, mais n'incrémente la version qu'une fois.

| Mutation | Sous-état modifié |
|---|---|
| verifyEmail / verifyPhone | Verification |
| changePassword | Credential |
| suspend / reactivate | suspended; suspend révoque aussi les rôles actifs |
| grantRole / revokeRole | RoleAssignment |
| grantConsent / withdrawConsent | Consent |
| replaceEmailVerification / replacePhoneVerification | Verification |

## Invariants de reconstitution

- identité et valeurs passent par leurs Value Objects;
- version non négative;
- deux Verification obligatoires et correctement canalisées;
- expiration strictement postérieure à l'émission;
- `verifiedAt` dans la fenêtre historique valide;
- révocation non antérieure à l'attribution;
- retrait non antérieur à l'accord;
- unicité d'un rôle actif par roleId;
- unicité d'un consentement actif par purpose;
- chronologie enfant cohérente avec `lastChangedAt`;
- aucun événement enregistré par l'hydratation.

Le code actuel ne valide pas encore toutes ces relations dans
`Account::reconstitute()`. Le mapper ne doit pas les inventer silencieusement :
4.9P-B doit attribuer précisément les validations de snapshot et les factories
de reconstitution.
