# F1 — Authority Matrix

| Lot | Autorité attendue | Source existante | État | Blocage |
|---|---|---|---|---|
| F1-A | `LoginIdentityResolverV1` | `AccountRegistry::find(AccountId)` seulement | MISSING | aucune résolution identifier → AccountId |
| F1-B | hashing credential versionné | `PasswordHash` conteneur seulement | MISSING | algorithme, paramètres et version non décidés |
| F1-C | `CredentialVerifierV1` | aucune | MISSING | aucun accès contrôlé au hash et aucun verifier plaintext |
| F1-D | autorité secret Session | colonne `secret_hash` seulement | MISSING | génération, encodage et vérification non décidés |
| F1-E | policy Session V1 | invariants documentaires | PARTIAL | durées, rotation et limite concurrente absentes |
| F1-F | réduction de Session | snapshots génériques du store | MISSING | aucun résultat applicatif fermé ni réduction temporelle |
| F1-G | transactions/concurrence | transaction atomique et optimistic locking présents | PARTIAL | aucune opération Session exécutable à orchestrer |
| F1-H | provisioning password | `RegisterAccount` accepte `PasswordHash` | PARTIAL | aucune autorité ne produit ce hash depuis un credential |

## Autorités à décider avant reprise

1. identifier(s) de login admis et normalisation owner-scoped ;
2. algorithme, paramètres, identifiant de version et politique de rehash credential ;
3. format, entropie, génération, hashing et rotation du secret Session ;
4. durée idle et durée absolue ;
5. condition/cadence de rotation ;
6. limite de sessions concurrentes ;
7. version de policy et règles de compatibilité des sessions historiques.
