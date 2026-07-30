# Phase 5.2C — Owner Source Persistence Impact

## Migration 062

`062_professional_mandate_owner_source.sql` est additive et crée uniquement le
schéma owner `professional_core` et deux tables nouvelles.

## Garanties

- migrations 001–061 inchangées ;
- aucune FK cross-domain ;
- aucune cascade ;
- aucun trigger ;
- aucune écriture dans IAM, Professional Status ou Professional Profile ;
- rollback complet par suppression des deux tables puis du schéma owner ;
- AccountId et ProfessionalId sont stockés comme UUID opaques ;
- la liste JSON ne contient que des ProfessionalId canoniques.

## Concurrence

Les remplacements sont sérialisés par advisory lock sur AccountId. La version
est contrôlée sous la même transaction. Les intents sont permanents et rendent
les retries identiques convergents.

## Rétention

La ligne courante représente uniquement les associations actives nécessaires à
la résolution. L’historique technique des intents ne contient ni mandat, ni
établissement, ni RepresentativeId.
