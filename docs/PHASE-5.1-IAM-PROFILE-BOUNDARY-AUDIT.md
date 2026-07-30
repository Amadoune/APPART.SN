# A-5.1-IAM-PROFILE-01 — Profile Boundary Audit

## 1. Frontières gelées

| Frontière | Contrainte |
|---|---|
| `Account` | id/email/phone/name readonly après register |
| `AccountRegistry` | find par id, add et save seulement |
| Snapshot V1 | identité, credential, verification, roles, consents figés |
| migration 042 | unicités email/téléphone historiques et tables Account |
| Account Status | Active/Suspended uniquement |
| Event V1 | suspended/reactivated uniquement |
| migrations 041/043 | workflow Status et owner Outbox inchangés |
| Runtime/HTTP | bindings et deux routes Status inchangés |
| consommateurs | aucun droit de supposer une mutation du Snapshot V1 |

## 2. Pourquoi la mutation directe est interdite

Ajouter `changeName`, `changeEmail` ou `changePhone` à `Account` :

- modifierait l'Aggregate gelé ;
- changerait le mapper et Snapshot V1 ;
- imposerait la revérification dans un modèle qui ne la prévoit pas ;
- modifierait les garanties d'unicité du repository ;
- introduirait de nouveaux Domain Events/consumers ;
- exigerait une recertification de 4.9P.

Modifier les colonnes 042 directement contournerait en plus optimistic locking,
les invariants et l'historique.

## 3. Frontière additive Profile

User Profile est un nouveau write model du même bounded context IdentityAccess,
mais il n'est pas une extension cachée de `Account`.

| Donnée | Owner après enrôlement Profile | Source historique |
|---|---|---|
| AccountId | Account, référencé immuablement | Snapshot V1 |
| nom affiché | UserProfile | seed `Account::name()` |
| email canonique 5.x | UserProfile + IdentityClaim | seed `Account::email()` |
| téléphone canonique 5.x | UserProfile + IdentityClaim | seed `Account::phone()` |
| credential | Account | Snapshot V1 |
| roles/consents | Account | Snapshot V1 |
| Account Status | Account Status Lifecycle | migration 041 |

Le seed doit être idempotent, conserver la valeur exacte et réserver toutes les
claims avant d'autoriser une mutation.

## 4. Unicité

La contrainte 042 reste vraie pour les identités historiques mais ne suffit
plus à l'identité canonique Profile. La future Claim Registry doit :

1. importer email/téléphone de tous les Accounts ;
2. normaliser avec une version explicite ;
3. réserver une claim même pendant une modification pending ;
4. empêcher deux Accounts de partager une claim active ou réservée ;
5. traiter rollback, expiration et course concurrente ;
6. ne jamais libérer automatiquement une claim historique sensible.

Une double vérification entre la table 042 et la Claim Registry serait
insuffisante sans transaction/ordre déterministe ; le cutover de l'autorité
doit donc être certifié avant les premières mutations.

## 5. Revérification et takeover

| Action | Preuve minimale |
|---|---|
| nom | session authentifiée + authorization propriétaire |
| email | réauthentification, possession nouvelle adresse, notification ancienne |
| téléphone | réauthentification, possession nouveau numéro, notification ancien canal |
| activation | expected version + claim réservée + challenge à usage unique |
| abandon | expiration/révocation tracée, aucune mutation canonique |

Les secrets/challenges ne figurent dans aucun événement.

## 6. Historique

ProfileRevision est append-only et conserve :

- revision id et expected/result version ;
- AccountId ;
- type de champ, ancienne/nouvelle valeur protégées selon rétention ;
- actor, occurredAt, reason/source ;
- résultat de vérification sans token ;
- checksum et correlation id.

La minimisation peut imposer hash/chiffrement et purge des anciennes valeurs.
Audit conserve la preuve de l'action, pas nécessairement la PII complète.

## 7. Consommateurs

| Consommateur | Comportement |
|---|---|
| Account Status 4.9 | inchangé, AccountId seulement |
| authentification 5.1 | Profile identity lookup après cutover |
| Professionals | Profile reader si besoin de contact, jamais Snapshot SQL |
| Listings/Leads/Reservations | conservent leurs IDs ; vue de contact via port |
| Notifications | consomme Profile event ou reader, sans token |
| Audit | actor AccountId stable ; PII non réécrite |

## 8. Projection

Une projection Profile peut être construite pour lecture publique/privée. Elle
reste reconstruisible depuis le store Profile et ne devient pas owner des
claims ou mutations.
