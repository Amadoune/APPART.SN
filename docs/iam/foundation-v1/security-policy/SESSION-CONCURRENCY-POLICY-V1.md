# Session Concurrency Policy V1

## Décision APPART.SN

Maximum : **5 sessions Active par Account** sous `session-policy-v1`.

Ce nombre permet téléphone personnel, ordinateur personnel, ordinateur professionnel et deux remplacements temporaires, tout en bornant l'exposition. Il s'agit d'une décision produit-sécurité APPART.SN, non d'une valeur imposée par OWASP.

## Dépassement

La création d'une sixième session révoque atomiquement la session Active la plus ancienne, puis crée la nouvelle session. Ordre déterministe : `originalIssuedAt`, puis SessionId UUID lexical. Les rows `Rotated`, `Revoked` et `Expired` ne comptent pas.

Aucune confirmation utilisateur, écran de gestion ou nouvelle fonctionnalité n'est introduite en V1.

## Concurrence

- verrou transactionnel owner-scoped par Account ;
- lecture du checkpoint et des sessions actives dans la même transaction ;
- limite évaluée après expiration mécanique des sessions arrivées à échéance ;
- expected version sur rotation/révocation ;
- deux créations concurrentes convergent vers au plus cinq sessions actives ;
- même intent et même checksum : `AlreadyApplied` sans second secret ;
- même intent divergent : `ReplayConflict` ;
- rollback externe préservé ; aucune session partielle.
