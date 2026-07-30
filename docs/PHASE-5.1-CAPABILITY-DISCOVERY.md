# Phase 5.1A — Identity & Access Completion Discovery

## 1. Décision d'entrée

```text
Phase 5.0B
→ GO CERTIFIÉ
→ FERMÉE

A-5.1-IAM-01
→ NO GO CERTIFIÉ
→ FERMÉ

A-5.1-IAM-PROFILE-01
→ GO CERTIFIÉ
→ FERMÉ

A-5.1-IAM-CLOSURE-01
→ GO CERTIFIÉ
→ FERMÉ

Phase 5.1 — Identity & Access Completion
→ OUVERTE
```

`A-5.1-IAM-ERASURE-01` reste identifié, non ouvert et hors périmètre.

Ce Discovery n'introduit aucun code. Il fixe la frontière avant Contracts.

## 2. Owner et capacités

Bounded context et owner uniques : `IdentityAccess`.

| Capacité additive | Autorité | Responsabilité |
|---|---|---|
| Authentication | Authentication Policy | credential verification, enumeration resistance, attempts et lockout |
| Sessions | Session | création, rotation, expiration, invalidation et concurrence |
| Password Recovery | Recovery Challenge | challenge dédié, usage unique et passage vers `ChangePassword` |
| User Profile | UserProfile | nom et contacts canoniques 5.x |
| Identity Claims | IdentityClaim | réservation/unicité email et téléphone |
| Contact Change | PendingContactChange | revérification et activation atomique |
| Profile History | ProfileRevision | historique append-only et anti-takeover evidence |
| Account Closure | AccountClosure | Requested, Closed, Reopened et rétention |

Le terme « autorité » ne préjuge pas des classes futures. Aucun nouvel
Aggregate n'est créé par le Discovery.

## 3. Réutilisation autorisée

- `AccountRegistry::find(AccountId)` pour obtenir un Account détaché ;
- `Account::isSuspended()`, verification/role/consent queries existantes ;
- `ChangePassword` et autres use cases historiques sans les modifier ;
- `AccountId` comme référence stable ;
- Snapshot V1 comme seed Profile/Claims en lecture seulement ;
- patterns de transaction, optimistic locking, idempotence et HTTP déjà
  certifiés, sans réutiliser leurs owners physiques.

## 4. Frontières gelées

Restent strictement inchangés :

- `Account`, `AccountRegistry`, Historical Account et Snapshot V1 ;
- migrations 041, 042 et 043 ;
- Account Status Workflow, Events V1, serializers, routing et delivery ;
- Account Status HTTP et middleware ;
- Account Status Outbox owner ;
- `PublicProjectionRuntimeServiceProvider` pour ses bindings certifiés ;
- Runtime Health `Healthy` à 58 requirements.

Une nouvelle capacité peut avoir son propre Provider/Runtime/Outbox/HTTP, mais
elle ne peut être injectée dans les composants gelés sans amendement.

## 5. Invariants pressentis

### Authentication

1. Une réponse publique ne révèle jamais si l'identifiant existe.
2. Le mot de passe clair ne traverse ni Domain, ni log, ni événement.
3. Une suspension ou fermeture refuse toute nouvelle session.
4. Lockout/attempt policy est distincte d'Account Status.
5. Un succès réinitialise seulement les compteurs autorisés par policy.

### Sessions

1. Le secret de session n'est stocké que hashé.
2. Rotation atomique : un ancien token ne redevient jamais valide.
3. Expiration absolue et idle sont explicites.
4. Logout et fermeture sont idempotents.
5. Password change, closure et décision de sécurité peuvent invalider toutes
   les sessions sans muter Account Status.

### Recovery

1. Challenge dédié, opaque, hashé, expirant et à usage unique.
2. Réponse publique non énumérante.
3. Consumption et password change sont atomiquement corrélés.
4. Les tokens de vérification Historical Account ne sont jamais réutilisés.

### Profile/Claims

1. `AccountId` est stable.
2. Nom, email et téléphone canoniques sont versionnés.
3. Une claim normalisée appartient au plus à un Account.
4. Une claim pending est réservée contre les courses.
5. Email/téléphone ne deviennent actifs qu'après preuve.
6. Snapshot V1 n'est jamais mis à jour par Profile.

### Closure

1. `Closed` est orthogonal à `Suspended`.
2. Closed prime pour l'accès, sans réactiver/suspendre Account.
3. Les sessions sont invalidées à la fermeture.
4. Les références cross-domain sont conservées.
5. Deleted/Anonymized/Erasure sont exclus.

## 6. États pressentis

| Capacité | États fermés au Blueprint |
|---|---|
| Attempt/Lock | Allowed, TemporarilyLocked |
| Session | Active, Rotated, Revoked, Expired |
| Recovery | Issued, Consumed, Expired, Revoked |
| Contact Change | Pending, Verified, Activated, Expired, Cancelled |
| Claim | Reserved, Active, Released selon policy certifiée |
| Closure | Open, ClosureRequested, Closed, Reopened |

Les noms techniques exacts deviennent contractuels seulement après le gate
Contracts.

## 7. Hors périmètre

- anonymisation, deletion physique, crypto-erasure et Privacy/Legal Erasure ;
- MFA, OAuth, SSO et social login ;
- changement de l'algorithme des hashes historiques sans gate dédié ;
- administration UI générale ;
- notification fournisseur complète hors messages de sécurité nécessaires ;
- modification de toute capacité gelée.

## 8. Verdict Discovery proposé

Owners, états, invariants, exclusions et coexistence sont suffisamment
déterminés pour ouvrir le gate Contracts après décision d'autorité.

```text
Phase 5.1A — Discovery / Blueprint
→ GO PROPOSÉ

Implémentation
→ AUCUNE DANS 5.1A
```
