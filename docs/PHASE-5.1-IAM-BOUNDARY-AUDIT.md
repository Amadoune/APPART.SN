# A-5.1-IAM-01 — Boundary Audit

## 1. Frontières observées

| Frontière | État réel | Protection |
|---|---|---|
| `Account` | Aggregate final ; email, téléphone et nom readonly ; credential, verifications, roles, consents et suspended mutables | gel 4.9/Historical Account |
| `AccountRegistry` | lookup par `AccountId`, add avec unicité id/email/phone, save optimiste | gelé |
| Historical Account | Snapshot V1 complet, mapping explicite, repository PostgreSQL | gelé |
| Account Status | Active/Suspended, bootstrap historique, orchestration atomique | gelé |
| Event V1 | `account.status.suspended`, `account.status.reactivated` | gelé |
| Outbox | owner IdentityAccess, migration 043, mapper/consumer génériques compatibles | gelé |
| Runtime | bindings Account/Status et Runtime Health 58 | gelé |
| HTTP | deux routes suspend/reactivate avec middleware d'autorité | gelé |
| projections | aucune projection d'authentification ou de profil certifiée | espace additif possible |

## 2. Invariants Account

L'Aggregate garantit :

- unicité durable id/email/téléphone via `AccountRegistry` et migration 042 ;
- chronologie non décroissante de toute mutation ;
- optimistic locking par `historical_version` ;
- impossibilité de changer mot de passe, vérification ou rôle lorsque suspendu
  selon les guards existants ;
- révocation automatique des rôles actifs lors d'une suspension ;
- unicité d'un rôle actif et d'un consentement actif ;
- deux canaux de vérification seulement : email et téléphone ;
- identité principale immuable après `register` ;
- absence d'état Closed/Deleted/Anonymized.

Ces invariants ne doivent pas être contournés par une future couche HTTP ou
session.

## 3. Capacité réelle de persistence

La migration 042 stocke déjà :

- identité et coordonnées ;
- hash de mot de passe et date de changement ;
- challenges de vérification email/téléphone ;
- historique ordonné des rôles ;
- historique ordonné des consentements ;
- statut historique suspendu et version.

Elle ne stocke pas :

- identifiant ou état de session ;
- challenge de récupération dédié ;
- compteur d'échecs, verrouillage ou MFA ;
- historique de login ;
- état de fermeture/anonymisation ;
- révisions d'identité/profil.

Ces absences n'autorisent pas l'extension de 042. Les données éphémères
d'authentification peuvent appartenir à un store additif. Les données de profil
et de fermeture modifiant la sémantique Account exigent un amendement.

## 4. Authentification

`Account::passwordMatches()` compare deux `PasswordHash` encodés. Il ne vérifie
pas un mot de passe en clair et ne doit pas recevoir celui-ci. Une future
authentification compatible doit :

1. résoudre email/téléphone vers `AccountId` par un reader IAM dédié ;
2. vérifier le secret dans un adapter de credential spécialisé ;
3. ne jamais exposer `encoded_password_hash` à Domain/HTTP/logs ;
4. charger `Account` par `AccountRegistry::find(AccountId)` pour appliquer
   suspended et verification policy ;
5. créer la session dans un store séparé.

Étendre `AccountRegistry` avec `findByEmail` ou exposer le hash depuis
`Account` est interdit sans amendement, mais n'est pas nécessaire.

## 5. Récupération

Les tokens existants sont des tokens de vérification d'email/téléphone ; leur
réutilisation implicite comme password-reset changerait leur sémantique gelée.
La récupération compatible utilise un challenge dédié et éphémère, puis
appelle le use case `ChangePassword` existant après preuve. Aucun nouvel event
Account Status ni ligne Outbox n'est requis.

## 6. Profil et fermeture

### Profil

La lecture de `id`, `email`, `phone`, `name`, verification/status/roles/consents
est compatible. La modification ne l'est pas :

- aucun comportement de mutation ;
- champs readonly ;
- unicités et revérification non définies ;
- Snapshot V1 sans historique de révision ;
- aucun événement de changement d'identité.

Le terme produit « profil utilisateur » inclut normalement modification ; la
fonction complète est donc classée incompatible.

### Fermeture

Suspendre n'est pas fermer. Une suppression SQL :

- contournerait l'Aggregate ;
- activerait des cascades non qualifiées ;
- détruirait la preuve historique ;
- laisserait des références cross-domain ;
- ne définirait ni anonymisation, ni rétention, ni invalidation de session.

La fermeture exige un état et une politique propres ; elle ne peut être
simulée par Account Status.

## 7. Boundary rules pour la future Phase 5.1

- aucune modification de `AccountStatus*` ;
- aucun nouvel event dans `AccountStatusEventType` ;
- aucune extension des payloads V1 ;
- aucune modification de 041–043 ;
- aucun ajout au catalogue Outbox Account Status ;
- aucune modification des deux routes gelées ;
- nouveaux stores auth/session isolés et migrations additives avec owner
  distinct si autorisés après amendements ;
- aucun nouveau requirement dans les 58 capacités sans phase Runtime dédiée ;
- aucune projection utilisée comme autorité de mutation.
