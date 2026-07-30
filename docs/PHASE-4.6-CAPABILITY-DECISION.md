# Phase 4.6 — Capability Decision

## Décision

La capacité retenue est **Media Item Lifecycle**.

Propriétaire unique : **Media**.

## Périmètre minimal

- création explicite hors workflow, avec état initial `Active` ;
- transitions de statut uniquement ;
- `Active + Remove → Removed` ;
- `Active + Archive → Archived` ;
- `Removed` et `Archived` sont terminaux ;
- aucune restauration, suppression physique, modification de contenu, réorganisation ou sélection du média principal dans le workflow.

## Motifs

Le Domain expose déjà les trois états et les deux mutations terminales. Le module possède son registre, son repository PostgreSQL, son transaction manager, ses identités et ses événements. La capacité peut donc progresser sans emprunter la logique de Property, Listing, Lead ou Professional Status.

Verdict Discovery : **GO proposé**. Le gate de contexte de collection reste obligatoire avant toute orchestration.
