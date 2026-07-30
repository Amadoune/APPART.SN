# Phase 5.1B — Account Availability Composition Contract

## 1. Owner

Owner : `IdentityAccess / Account Availability Composition`.

Il compose des décisions existantes sans devenir owner d'Account Status ou
Closure et sans persister un troisième statut.

## 2. Query

`InspectAccountAvailability(AccountId, purpose, observedAt)`.

Purposes V1 :

- Authenticate
- RenewSession
- RecoverPassword
- MutateProfile
- RequestClosure
- ReopenClosure

## 3. Résultats fermés

| Résultat | Cause interne |
|---|---|
| Available | Account existe, Status Active, Closure Open/Reopened selon modèle |
| UnavailableSuspended | Status Suspended |
| UnavailableClosed | Closure Requested/Closed selon purpose |
| AccountMissing | Account absent |
| Inconsistent | sources présentes mais combinaison impossible |
| Indeterminate | source indisponible |

Les causes internes sont mappées vers une réponse publique générique par
Authentication/Recovery.

## 4. Précédence

1. source indisponible/incohérente → Indeterminate/Inconsistent, fail closed ;
2. Account absent → AccountMissing ;
3. Closure Closed → UnavailableClosed ;
4. Status Suspended → UnavailableSuspended ;
5. purpose-specific policy ;
6. Available.

Reopened ne neutralise jamais Suspended.

## 5. Dépendances

Lectures uniquement :

- Account existence par `AccountRegistry::find(AccountId)` ;
- Account Status par son port certifié/read result ;
- Closure par `InspectClosureState`.

Availability ne dépend pas de Sessions, Authentication, Recovery ou Profile.
Ainsi, Authentication → Availability → Closure ne forme aucun cycle.

## 6. Invariants

1. aucune persistence propre ;
2. aucune mutation ;
3. fail closed ;
4. résultats déterministes pour mêmes observations/policy version ;
5. source versions et observedAt conservés dans le diagnostic interne ;
6. aucune PII ;
7. aucune reconstruction d'un nouveau status ;
8. consumer ne transforme pas Indeterminate en Available.

## 7. Version et compatibilité

Contract V1. Ajouter un purpose ou modifier la precedence exige évolution
versionnée. Runtime futur séparé ; catalogue Health 58 inchangé.
