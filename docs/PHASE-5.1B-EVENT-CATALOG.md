# Phase 5.1B — Event Catalog

## 1. Politique

Un événement n'est retenu que s'il existe un consumer cross-boundary légitime.
Les logs de sécurité, attempts, secrets de session et challenges restent dans
leurs stores/audits privés.

Ce catalogue est normatif au niveau sémantique. Classes, transport, routing et
Outbox appartiennent à 5.1G/5.1H.

## 2. Événements V1 retenus

| Event Owner | Type logique | Payload minimal | Consumers pressentis |
|---|---|---|---|
| User Profile | `identity.profile.name_changed` | eventId, AccountId, profileVersion, occurredAt | audit/private profile projections |
| User Profile | `identity.profile.email_changed` | eventId, AccountId, profileVersion, occurredAt | auth identity source, notifications |
| User Profile | `identity.profile.phone_changed` | eventId, AccountId, profileVersion, occurredAt | auth identity source, notifications |
| Account Closure | `identity.account.closure_requested` | eventId, AccountId, closureVersion, occurredAt | notifications/audit |
| Account Closure | `identity.account.closed` | eventId, AccountId, closureVersion, occurredAt | sessions checkpoint, Listing/Lead/Reservation/Favorites policies |
| Account Closure | `identity.account.reopened` | eventId, AccountId, closureVersion, occurredAt | dependent availability projections |

## 3. Événements non retenus en V1

- AuthenticationSucceeded/Failed/Locked ;
- SessionCreated/Renewed/Expired ;
- RecoveryRequested ;
- ClaimReserved/Activated ;
- ContactChangePending/Verified ;
- ProfileRevisionAppended.

Ils n'ont pas de consumer cross-boundary suffisant ou exposeraient une surface
de sécurité excessive. Les opérations restent observables par métriques et
audit privé.

`PasswordRecoveryCompleted` reste différé ; password/session orchestration peut
être auditée sans event public.

## 4. Enveloppe sémantique

Tous les events retenus possèdent :

- eventId déterministe ;
- type et payloadVersion V1 ;
- AccountId ;
- aggregate/profile/closure version résultante ;
- occurredAt et recordedAt ;
- correlationId/causationId ;
- owner `IdentityAccess.Profile` ou `IdentityAccess.Closure` ;
- checksum canonique.

## 5. Confidentialité

Interdits :

- nom, email, téléphone et leurs fingerprints ;
- password/hash ;
- token/challenge/session id ou secret ;
- IP/device/user-agent ;
- reason libre, legal hold ou diagnostic interne ;
- ancienne/nouvelle valeur.

Les consumers qui ont besoin de la nouvelle coordonnée utilisent un reader
autorisé après réception du fait.

## 6. Idempotence/versionnement

EventId dérivé de l'opération appliquée, jamais aléatoire au retry. Même
eventId/payload/checksum est idempotent ; divergence est quarantinée. Toute
PII ajoutée ou changement de sémantique exige V2.

## 7. Compatibilité

Catalogue distinct de `AccountStatusEventType`. Aucun type ou payload ajouté à
Account Status V1, Generic Delivery ou migration 043 pendant 5.1B.
