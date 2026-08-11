# Session Policy Versioning V1

## Identifiants normatifs

- policy Session : `session-policy-v1` ;
- secret : `session-secret-hmac-sha256-v1` ;
- credential : `credential-hash-v1`.

## Portage

Chaque nouvelle Session porte explicitement :

- `policy_version` ;
- `secret_hash_version` et `key_id` dans la représentation de preuve ;
- `original_issued_at` ;
- `idle_expires_at` ;
- `absolute_expires_at`.

Le verdict est calculé selon la version stockée avec la session, jamais selon la seule policy courante. Une policy ou clé inconnue produit `DependencyUnavailable` interne et une session invalide publiquement.

## Passage futur à V2

- V2 émet de nouvelles sessions sous son identifiant ;
- V1 reste évaluée jusqu'à sa deadline absolue ou révocation explicite ;
- aucun allongement rétroactif ;
- une révocation globale peut accélérer la sortie de V1 ;
- aucune réécriture de l'historique ni backfill de policy.

Les sessions historiques sans `policy_version` sont `historical-unqualified` et invalides fail-closed. L'utilisateur doit se réauthentifier ; aucune policy n'est déduite de `expires_at`.
