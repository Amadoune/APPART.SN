# Historical Account Snapshot V1 — Invariant Matrix

| Invariant | Propriétaire | Résultat |
|---|---|---|
| snapshotVersion = 1 | type V1 | fermé par construction |
| version historique >= 0 | snapshot racine | rejet |
| channel email à la position email | snapshot racine | rejet |
| channel phone à la position phone | snapshot racine | rejet |
| exactement deux Verification | constructeur racine typé | obligatoire |
| expiresAt > issuedAt | Verification snapshot | rejet |
| issuedAt <= verifiedAt < expiresAt | Verification snapshot/factory | rejet |
| Credential.changedAt <= lastChangedAt | snapshot racine | rejet |
| revokedAt >= grantedAt | RoleAssignment snapshot/factory | rejet |
| withdrawnAt >= grantedAt | Consent snapshot/factory | rejet |
| ordinal continu et conforme à l'ordre | snapshot racine | rejet |
| au plus un rôle actif par roleId | snapshot racine | rejet |
| au plus un consent actif par purpose | snapshot racine | rejet |
| dates enfant <= lastChangedAt | snapshot racine | rejet |
| secret non vide | SensitivePersistenceValueV1 | rejet |
| identité/format secret lisible | Value Objects lors de l'hydratation | rejet |
| aucun événement après hydratation | Account::reconstitute + test | `[]` |
| événements en attente non snapshotés | frontière | exclusion |

La frontière ne normalise, ne complète et ne réordonne aucune entrée invalide.
