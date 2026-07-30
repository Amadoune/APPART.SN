# A-5.1-IAM-01 — Compatibility Matrix

## 1. Verdict par fonctionnalité

| Fonction | Account/invariants | Registry/Snapshot 042 | Event V1/Outbox | Runtime/HTTP gelé | Conclusion |
|---|---|---|---|---|---|
| Authentification | lecture status/verifications ; verifier externe | reader credential interne additif | aucun event requis | tranche additive séparée | **Compatible avec les capacités gelées** |
| Connexion | policy + création de session hors Account | lookup IAM additif, session store séparé | aucun event requis | nouvelles routes distinctes | **Compatible avec les capacités gelées** |
| Récupération | challenge séparé puis `ChangePassword` | aucun changement 042 ; store challenge additif | `PasswordChanged` historique, pas Event Status V1 | endpoints distincts | **Compatible avec les capacités gelées** |
| Changement de mot de passe | comportement/use case existants | Snapshot V1 sait persister credential | ne pas router dans Account Status Outbox | endpoint distinct | **Compatible avec les capacités gelées** |
| Profil utilisateur | lecture compatible ; mutation absente | champs principaux immuables, pas de révision | événements absents | endpoint de mutation non définissable | **Amendement supplémentaire requis** |
| Fermeture de compte | aucun état/transition de fermeture | ni tombstone, ni anonymisation/rétention | événement et effets absents | conflit potentiel avec Status HTTP | **Amendement supplémentaire requis** |
| Rôles | grant/revoke/use cases existants | historique et unicité existants | events Domain historiques ; pas Event Status V1 | endpoints IAM distincts | **Compatible avec les capacités gelées** |
| Consentements | grant/withdraw/use cases existants | historique et unicité existants | events Domain historiques ; pas Event Status V1 | endpoints IAM distincts | **Compatible avec les capacités gelées** |
| Vérifications d'identité | email/téléphone seulement, méthodes existantes | deux canaux Snapshot V1 | events Domain historiques ; pas Event Status V1 | endpoints IAM distincts | **Compatible avec les capacités gelées** |
| Sessions | hors Aggregate Account | store séparé, aucune modification 042 | events non requis pour le socle | runtime/HTTP séparés | **Compatible avec les capacités gelées** |

## 2. Limites des verdicts compatibles

« Compatible » signifie qu'une conception additive existe. Cela ne signifie
pas que le code est déjà présent ni que Phase 5.1 est ouverte.

- Authentification ne couvre pas MFA, SSO ou social login.
- Connexion ne permet pas de modifier `AccountRegistry`.
- Récupération ne réutilise pas les tokens de vérification.
- Vérifications d'identité signifie seulement email et téléphone ; KYC,
  document ou biométrie relève d'une autre capacité/amendement.
- Sessions n'ajoutent aucun requirement au Runtime Health gelé.
- Les nouveaux contrats, stores, migrations additives et routes de 5.1 ne
  pourront être créés qu'après son ouverture et les gates applicables.

## 3. Matrice des écritures

| Fonction | Écriture Account autorisée | Écriture store additif | Écriture interdite |
|---|---|---|---|
| auth/login | aucune | session, tentative/limite si cadrée | tables Status/Outbox |
| recovery | `changePassword` via use case seulement | challenge recovery | verification token existant réinterprété |
| password | `changePassword` via `AccountRegistry::save` | invalidation sessions | SQL credential direct |
| roles | grant/revoke via use cases | aucune nécessaire | role table direct |
| consents | grant/withdraw via use cases | preuve delivery éventuelle | consent table direct |
| verifications | verify/replace via use cases | delivery challenge | verification table direct |
| profile | aucune avant amendement | read model seulement | identité Account |
| closure | aucune avant amendement | aucune | delete/suspend-as-close |
| sessions | aucune | session store | migration 042 |
