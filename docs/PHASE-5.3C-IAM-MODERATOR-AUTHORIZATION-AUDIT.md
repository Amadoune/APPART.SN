# Phase 5.3C — IAM Moderator Authorization Audit

## Gate

Décider si un `AccountId` est autorisé à :

- `Report` ;
- `Validate` ;
- `Investigate` ;
- `Decide` ;
- `Audit`.

Résultats requis : `Allowed`, `Denied`, `Corrupted`,
`DependencyUnavailable`.

## État réel du dépôt

Identity & Access contient :

- `RoleAssignment`, `RoleId`, `RoleGranted` et `RoleRevoked` dans le Domain
  historique ;
- les use cases concrets `GrantRole` et `RevokeRole` ;
- des snapshots et mappings propriétaires de rôles ;
- `AccountAvailabilityInspector`, limité aux purposes IAM :
  `Authenticate`, `RenewSession`, `RecoverPassword`, `MutateProfile`,
  `RequestClosure` et `ReopenClosure`.

La lecture d'un rôle exige aujourd'hui de charger l'Aggregate, son snapshot ou
ses tables. Ces trois chemins sont interdits à 5.3.

## Frontière existante

Aucun contrat public versionné n'expose une décision d'autorisation par action
de modération.

`AccountAvailabilityInspector` décide de la disponibilité d'un compte pour des
purposes IAM. Il ne décide ni d'un rôle de modération ni de la séparation des
acteurs. Son résultat expose en outre des états et versions internes qui ne
doivent pas devenir le contrat d'autorisation 5.3.

## Compatibilité

| Exigence | État |
|---|---|
| contrat public V1 | Absent |
| résultats fermés requis | Absents |
| fail-closed | Non démontrable pour la modération |
| indépendant du Runtime | Aucun contrat adapté |
| aucune lecture d'Aggregate | Non réalisable avec les accès actuels |
| cinq actions distinctes | Absentes |

## Décision

**NO GO — amendement versionné requis.**

## Conséquence

- le signalement authentifié ne peut pas encore décider `Report` via cette
  frontière ;
- les opérations privées Validate/Investigate/Decide/Audit restent désactivées ;
- aucune lecture directe de `Account`, `RoleAssignment`, snapshot ou SQL n'est
  autorisée ;
- 5.3 ne définit ni ne modifie les rôles IAM.

## Amendement identifié

`A-5.3-IAM-MODERATOR-AUTHORIZATION-01`

Objet minimal : un reader owner IAM, read-only et versionné recevant
`AccountId + ModerationCapability + observedAt`, avec le catalogue fermé
`Allowed`, `Denied`, `Corrupted`, `DependencyUnavailable`.

**Statut : IDENTIFIÉ — NON OUVERT.**
