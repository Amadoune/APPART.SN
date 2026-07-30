# Phase 4.7B-R2 — Historical Mirror Mutation Contract

Le payload V1 transporte exactement la transition certifiée, `AdministrativeActionId`, la version historique attendue, l'acteur, le motif historique requis, `occurredAt` UTC et les identités enfants requises.

| Mutation | ApprovalId | DecisionId | Acteur / motif / occurredAt |
|---|---|---|---|
| Record | interdit | interdit | obligatoires |
| Approve | obligatoire | obligatoire | obligatoires |
| Reject | interdit | obligatoire | obligatoires |

Les factories refusent une action différente de celle portée par la transition exacte. Elles ne reconstruisent jamais une transition.

Le checksum SHA-256 couvre tous les champs dans un ordre canonique, y compris le contenu historique du motif. Le motif reste réservé au miroir historique et ne devient ni donnée Workflow ni événement.
