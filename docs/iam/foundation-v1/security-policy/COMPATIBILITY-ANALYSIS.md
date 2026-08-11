# Compatibility Analysis

## Accounts et credentials

Les Accounts et `PasswordHash` historiques restent lisibles. La colonne `encoded_password_hash varchar(255)` accepte le format Argon2id V1. Les bcrypt PHP recevables sont vérifiés puis rehashés uniquement après succès. Aucun hash n'est transformé hors authentification et aucun plaintext n'est reconstruit.

## Sessions

La migration 045 ne distingue pas idle et absolute expiration et ne porte ni policy, ni secret hash version. Elle reste strictement inchangée.

F1 nécessitera une migration additive et réversible qualifiée pour ajouter :

- `policy_version` ;
- `original_issued_at` ;
- `idle_expires_at` ;
- `absolute_expires_at` ;
- une représentation explicite de version/key id du secret si elle n'est pas entièrement encodée dans `secret_hash`.

Les colonnes nouvelles doivent être nullable pour les rows historiques lors du déploiement. Seules les sessions entièrement qualifiées sont valides ; les rows historiques restent consultables pour audit mais sont rejetées à l'inspection. Aucun backfill ne leur attribue `session-policy-v1`.

## Login resolver

Une lecture owner-scoped par email et téléphone normalisés devra être ajoutée au port/persistence IAM. Les contraintes uniques historiques existantes supportent l'unicité, mais aucune lecture SQL ne devra apparaître dans HTTP ou Application.

## Cookie et HTTP

La policy préserve `__Host-appart_session`, Secure, HttpOnly, SameSite=Strict, Path `/`, aucun Domain. F2 reste non ouverte ; le binding fail-closed reste actif.
