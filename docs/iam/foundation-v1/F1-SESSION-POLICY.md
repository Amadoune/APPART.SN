# F1 — Session Policy

## Décisions déjà certifiées

- Session V1 sans remember-me ;
- états `Active`, `Rotated`, `Revoked`, `Expired` ;
- expiration idle et absolue obligatoires ;
- rotation atomique créant une nouvelle session et invalidant l'ancien secret ;
- logout idempotent ;
- invalidation globale par checkpoint monotone ;
- secret uniquement sous forme de hash dans la persistence ;
- vérification de disponibilité du compte à l'inspection ;
- concurrence régie par une `policyVersion`.

## Décisions absentes

| Paramètre | État |
|---|---|
| durée idle | MISSING |
| durée absolue | MISSING |
| seuil/cadence de rotation | MISSING |
| nombre maximal de sessions actives | MISSING |
| version de policy Session exécutable | MISSING |
| rétention des sessions terminales | MISSING |
| format et entropie du secret opaque | MISSING |
| algorithme/version du hash de secret | MISSING |

Les valeurs 15 minutes, 30 jours ou toute autre valeur conventionnelle sont expressément non retenues. La policy reste `NOT_IMPLEMENTED` jusqu'à décision d'autorité explicite.
