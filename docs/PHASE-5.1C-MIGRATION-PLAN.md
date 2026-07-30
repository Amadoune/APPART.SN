# Phase 5.1C — Migration Plan

## 1. Inventaire

Toutes les migrations sont nouvelles, additives et sous
`src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations`.

| N° | Migration | Owner | Tables |
|---:|---|---|---|
| 044 | `authentication_attempts` | Authentication Attempts | `identity_access_completion.authentication_attempts` |
| 045 | `sessions` | Sessions | `sessions`, `session_invalidation_checkpoints` |
| 046 | `password_recovery` | Password Recovery | `recovery_challenges` |
| 047 | `user_profiles` | User Profile | `user_profiles` |
| 048 | `identity_claims` | Identity Claims | `identity_claims` |
| 049 | `pending_contact_changes` | Contact Changes | `pending_contact_changes` |
| 050 | `profile_revisions` | Profile Revisions | `profile_revisions` |
| 051 | `account_closures` | Account Closure | `account_closures` |

Le schéma `identity_access_completion` matérialise l'ownership additif. Il ne
remplace pas `identity_access`.

## 2. Ordre

```text
044 Attempts
→ 045 Sessions
→ 046 Recovery
→ 047 Profiles
→ 048 Claims
→ 049 Contact Changes
→ 050 Revisions
→ 051 Closures
```

Cet ordre n'autorise aucun seed. 047–048 restent vides jusqu'à 5.1D.

## 3. Contraintes physiques

- UUID validés par PostgreSQL ;
- enums par CHECK explicites et fermés V1 ;
- versions `bigint >= 0` ;
- timestamps et chronologies par CHECK ;
- checksums hexadécimaux de longueur 64 ;
- intent ids bornés et uniques dans le scope owner requis ;
- fingerprints HMAC bornés, jamais valeurs claires ;
- uniques partielles pour challenges/contact changes actifs ;
- unique claim type/fingerprint permanente ;
- unique Account/profile version pour revisions ;
- aucune FK en dehors du schéma owner ;
- en particulier aucune FK vers `identity_access.accounts`.

## 4. Rollback

Chaque migration possède un `.down.sql` isolé qui supprime uniquement ses
objets. L'ordre inverse est 051 → 044.

Gates rollback :

1. 041–043 existent avant ;
2. appliquer 044–051 ;
3. insérer fixtures ;
4. rollback ciblé d'une migration sans toucher aux autres owners non
   dépendants ;
5. rollback inverse complet ;
6. vérifier 041–043 bit-for-bit structurellement présents ;
7. réappliquer 044–051.

Un rollback de production avec données exige sauvegarde/export owner et n'est
jamais une cascade vers Historical Account.

## 5. Numérotation réservée

Les numéros 044–051 sont réservés par 5.1C. Toute collision découverte avant
implémentation suspend la phase et exige un nouveau plan versionné ; aucun
renommage silencieux.

## 6. Aucune migration interdite

Ne sont créées/modifiées dans 5.1C :

- aucune migration Runtime, HTTP, Delivery ou Outbox ;
- aucune migration seed/cutover ;
- aucune migration Erasure ;
- aucune modification 041, 042 ou 043.
