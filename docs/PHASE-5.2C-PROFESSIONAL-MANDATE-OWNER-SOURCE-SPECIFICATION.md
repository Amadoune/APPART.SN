# Phase 5.2C — Professional Mandate Owner Source Specification

## Statut proposé

**IMPLÉMENTATION CONFORME — GO À DÉTERMINER APRÈS PREUVE POSTGRESQL.**

## Owner

La source appartient exclusivement à Professional Core. Elle ne remplace ni
l’Aggregate Professional, ni ses mandats ; elle matérialise la seule vue
canonique minimale nécessaire à la résolution publique future.

## Modèle exposé

Entrée :

- référence Account UUID opaque.

Résultat fermé :

- `Resolved(ProfessionalId)` ;
- `NotMandated` ;
- `Ambiguous` ;
- `Corrupted` ;
- `DependencyUnavailable`.

La source n’expose jamais `RepresentativeId`, mandat, rôle, établissement,
historique ou Aggregate.

## Modèle persistant

Schéma owner `professional_core` :

| Table | Responsabilité |
|---|---|
| `mandate_owner_sources` | ensemble canonique trié des ProfessionalId actifs par Account |
| `mandate_owner_source_intents` | historique permanent des intents |

L’ensemble vide produit `NotMandated`, une cible produit `Resolved`, plusieurs
cibles produisent `Ambiguous`. Un ordre, doublon ou identifiant invalide produit
`Corrupted` ou un rejet d’écriture.

## Écritures owner

`ProfessionalMandateOwnerSourceUpdater` remplace atomiquement la vue canonique
d’un Account avec :

- optimistic locking ;
- intentId et checksum ;
- `Applied`, `AlreadyApplied`, `DivergentIntent`, `VersionConflict`, `Rejected` ;
- advisory lock transactionnel ;
- savepoint lors d’une transaction englobante ;
- rollback intégral.

L’updater est une capacité owner interne. Il n’est pas le resolver public et
n’est pas bindé dans ce sprint.

## Frontières

- aucune FK cross-domain ;
- aucune cascade ;
- aucune lecture IAM ;
- aucune lecture de l’Aggregate ;
- aucun Event replay ;
- aucun Runtime, provider, binding ou HTTP ;
- migrations 001–061 inchangées.
