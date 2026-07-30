# Phase 5.3C — Administration Audit Append Boundary Audit

## Contrat attendu

Une frontière owner Administration Audit permettant conceptuellement :

`append(recordV1)`

avec :

- payload minimal ;
- append-only ;
- record identity et checksum déterministes ;
- `Applied`, `AlreadyApplied`, `DivergentRecord`, `Rejected`,
  `DependencyUnavailable` ;
- aucune dépendance à un Aggregate, store, SQL ou Runtime interne.

## État réel du dépôt

Le domaine Administration Audit contient :

- `AdministrativeActionRegistry` ;
- l'Aggregate `AdministrativeAction` ;
- les use cases concrets `CreateAdministrativeAction` et
  `RecordAdministrativeAction` ;
- `AdministrativeActionLifecycleWorkflowStore` ;
- `AdministrativeActionContextualTransitionStore::append()` ;
- les repositories PostgreSQL et mappers propriétaires.

## Frontière existante

`AdministrativeActionRegistry` expose :

- `find()` retournant un Aggregate détaché ;
- `add()` et `save()` ;
- des exceptions de collision et concurrence.

Les méthodes `append()` trouvées appartiennent aux stores de lifecycle et de
transition contextuelle. Elles ne constituent pas une frontière publique
cross-domain.

## Compatibilité

| Exigence | État |
|---|---|
| contrat public V1 | Absent |
| append minimal | Seulement dans des stores internes |
| résultats fermés | Exceptions/écritures internes |
| idempotence recordId + checksum | Non exposée publiquement |
| append-only | Invariant interne existant |
| aucune exposition d'Aggregate | Non satisfait par Registry |
| indépendance Runtime/Persistence | Non satisfaite pour l'usage 5.3 |

## Décision

**NO GO — amendement versionné requis.**

## Conséquence

- Moderation peut conserver son historique métier propriétaire ;
- il ne peut pas écrire directement l'Aggregate ou les stores Administration
  Audit ;
- l'audit transverse reste désactivé jusqu'à certification de la frontière ;
- aucun événement candidat 5.3 ne devient automatiquement une commande d'audit.

## Amendement identifié

`A-5.3-AUDIT-APPEND-BOUNDARY-01`

Objet minimal : contrat owner Administration Audit, read/write append-only,
versionné, idempotent et sans Aggregate, avec payload fermé et minimal.

**Statut : IDENTIFIÉ — NON OUVERT.**
