# Session Lifetime & Rotation Policy V1

## Comparaison

| Option | Avantage | Risque / coût | Décision |
|---|---|---|---|
| idle 15 min | fenêtre de vol réduite | interruptions fréquentes en authoring | rejetée |
| idle 30 min | borne OWASP basse-risque, parcours propriétaire viable | fenêtre plus longue que 15 min | retenue |
| idle 60 min | confort | exposition excessive sans besoin produit | rejetée |
| absolue 4 h | fenêtre limitée | coupe une session de travail courante | rejetée |
| absolue 8 h | journée de travail bornée, plage OWASP | fenêtre maximale de la plage | retenue |
| absolue 24 h | confort multi-jour | incompatible avec absence de remember-me V1 | rejetée |

## Décision temporelle

- idle timeout : 30 minutes après `lastSeenAt` ;
- absolute lifetime : 8 heures après `originalIssuedAt` ;
- contrôles serveur à chaque inspection, avant toute mise à jour de `lastSeenAt` ;
- égalité à la deadline : expirée ;
- `lastSeenAt` ne dépasse jamais l'expiration absolue ;
- aucune horloge client ; l'instant d'observation est fourni par l'autorité applicative UTC.

## Rotation

Rotation obligatoire :

1. à l'authentification, par émission d'une session nouvelle sans réutiliser un identifiant pré-authentifié ;
2. lorsque l'âge du secret courant atteint 30 minutes ;
3. après tout changement de privilège effectivement appliqué ;
4. après une réauthentification fraîche explicitement exigée.

Un changement de mot de passe, Recovery, Account Closure ou décision Security invalide toutes les sessions via checkpoint plutôt que de les faire tourner.

La rotation est atomique, conserve `originalIssuedAt` et l'expiration absolue initiale, recalcule l'idle depuis l'instant de rotation sans dépasser l'absolue, et invalide immédiatement l'ancien secret. Deux rotations concurrentes : un seul gagnant par expected version ; l'autre reçoit `VersionConflict`/relecture, jamais un second secret.

Référence : [OWASP Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html).
