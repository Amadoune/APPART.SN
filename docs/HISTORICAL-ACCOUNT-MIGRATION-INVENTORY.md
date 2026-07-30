# Historical Account — Migration Inventory

## Migration certifiée

```text
042_historical_account_persistence
```

La migration est additive, PostgreSQL-spécifique et ne modifie ni la migration
041 ni `identity_access.account_status_lifecycle_transitions`.

## Objets créés

| Objet | Clé / contraintes principales | Données |
|---|---|---|
| `identity_access.accounts` | PK `account_id`; uniques `email`, `phone`; version historique positive; snapshot V1 | Racine Account historique |
| `identity_access.account_credentials` | PK/FK `account_id`, cascade | Hash encodé et instant avec offset |
| `identity_access.account_verifications` | PK `(account_id, channel)`; canaux fermés; chronologie | Email et téléphone, tokens et instants |
| `identity_access.account_role_assignments` | PK `(account_id, ordinal)`; un rôle actif unique | Historique RoleAssignment |
| `identity_access.account_consents` | PK `(account_id, ordinal)`; un consentement actif unique | Historique Consent |

Tous les enfants référencent la racine avec `ON DELETE CASCADE`. Les offsets
temporels sont persistés explicitement dans l'intervalle -840 à +840 minutes.
Les ordinals des historiques RoleAssignment et Consent sont conservés.

## Atomicité et concurrence

- `add()` écrit racine et enfants dans une même transaction.
- `save()` applique un unique `UPDATE` conditionné par
  `historical_version = expectedVersion`, puis remplace les enfants dans la
  même transaction.
- Une transaction créée par le Repository est commitée ou rollbackée par lui.
- Une transaction externe est rejointe; elle n'est ni commitée ni rollbackée
  par le Repository.
- Une concurrence sur la même version produit exactement un succès et un
  conflit.
- Les violations d'unicité AccountId, email et téléphone sont traduites en
  refus fermés.

## Ordre partagé et rollback

```text
Application
041 up
→ 042 up

Rollback
042 down
→ 041 down
```

Le rollback 042 supprime exclusivement, dans l'ordre des dépendances, Consent,
RoleAssignment, Verification, Credential puis Account. La migration 041 reste
inchangée.

## Gel

Après GO FINAL 4.9P, toute modification des scripts 042, de leur ordre, de
leurs tables ou contraintes exige un amendement versionné et une nouvelle
certification.
