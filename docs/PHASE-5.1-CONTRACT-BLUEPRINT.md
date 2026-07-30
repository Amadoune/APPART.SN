# Phase 5.1A — Contract Blueprint

## 1. Règle

Ce document définit les familles de contrats attendues. Il ne crée aucune
interface PHP, payload ou route.

## 2. Commands et résultats

| Capacité | Commands pressenties | Résultats fermés minimaux |
|---|---|---|
| Auth | Authenticate | Authenticated, InvalidCredentials, Locked, AccountUnavailable |
| Session | Create, Renew, Logout, InvalidateAll | Applied, AlreadyApplied, Expired, Conflict, Rejected |
| Recovery | Request, Consume, Revoke | Accepted, Applied, InvalidOrExpired, AlreadyConsumed, Conflict |
| Profile | Enroll, ChangeName, RequestContactChange, Verify, Activate, Cancel | Applied, Pending, AlreadyInState, ClaimConflict, InvalidProof, VersionConflict |
| Closure | Request, Confirm, Reopen | Applied, AlreadyInState, RetentionBlocked, AccountUnavailable, VersionConflict |

Les réponses Request Recovery et Authenticate sont non énumérantes au bord
HTTP, même si le diagnostic interne est plus précis.

## 3. Ports owners

| Port futur | Owner | Interdiction |
|---|---|---|
| CredentialVerifier | Authentication | ne retourne jamais le hash |
| LoginIdentityResolver | Authentication/Profile | n'étend pas AccountRegistry |
| AuthenticationAttemptStore | Authentication | ne modifie pas Account Status |
| SessionStore | Sessions | secrets non persistés en clair |
| RecoveryChallengeStore | Recovery | distinct des verifications 042 |
| UserProfileStore | Profile | aucune écriture Historical Account |
| IdentityClaimRegistry | Claims | aucune double autorité après cutover |
| PendingContactChangeStore | Profile | challenge à usage unique |
| ProfileRevisionStore | Profile | append-only |
| AccountClosureStore | Closure | orthogonal au workflow Status |
| AccountAvailabilityReader | IAM composition | combine status + closure sans les réécrire |

## 4. Events futurs

Catalogues distincts, à versionner au gate Event Contract :

- SessionCreated/Renewed/Revoked/Expired si un consumer justifié existe ;
- PasswordRecoveryCompleted, sans token ni identifiant de login ;
- ProfileNameChanged ;
- ProfileEmailChanged/ProfilePhoneChanged après activation uniquement ;
- AccountClosureRequested/Closed/Reopened.

Les tentatives d'authentification détaillées relèvent d'un audit sécurité privé,
pas d'un événement public. Aucun event n'est ajouté au catalogue Account
Status V1.

## 5. Identity et idempotence

Toute mutation reçoit :

- operation/intent id ;
- AccountId ;
- expected version lorsque l'autorité est versionnée ;
- actor et occurredAt explicites ;
- correlation id ;
- preuve de rejeu inspectable.

Recovery/contact challenges utilisent un identifiant opaque, un secret hashé
et un consumption id atomique.

## 6. Confidentialité

Interdits dans events, diagnostics HTTP et logs :

- mot de passe ou hash ;
- session/recovery/verification token ;
- email/téléphone complets sauf canal sécurisé strictement nécessaire ;
- raison interne de lockout permettant l'énumération ;
- PII historique de ProfileRevision.

## 7. Gate Contracts

Avant code, chaque contrat devra prouver :

- owner unique ;
- états/résultats exhaustifs ;
- invariants et erreurs stables ;
- idempotence/concurrence ;
- données sensibles minimisées ;
- compatibilité Account/Snapshot/Status gelés ;
- stratégie de version et consommateurs.
