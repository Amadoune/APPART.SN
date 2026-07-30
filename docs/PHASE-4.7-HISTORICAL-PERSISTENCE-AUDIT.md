# Phase 4.7B-R1 — Historical Persistence Audit

## Registre certifié existant

`AdministrativeActionRegistry` expose `find`, `add` et `save`. Son repository PostgreSQL reconstitue l'Aggregate complet depuis quatre tables historiques :

- `administrative_actions` ;
- `administrative_action_approvals` ;
- `administrative_action_decisions` ;
- `administrative_action_audit_entries`.

Le root est mutable sous contrôle de version optimiste. Les approbations, décisions et entrées d'audit sont ajoutées sans réécriture. La transaction historique possède actuellement son propre cycle `begin/commit/rollback`.

## Données propriétaires

Le registre historique reste l'unique propriétaire de la création, du texte du motif, de la cible, du type d'action, de la décision four-eyes matérialisée, des approbations, décisions et détails d'audit.

Le futur journal Lifecycle possédera exclusivement l'ordre append-only des transitions et l'état courant Lifecycle des actions enrôlées.

## Incompatibilité anticipée

Le repository historique ne peut pas être appelé tel quel à l'intérieur d'une transaction externe puisqu'il ouvre sa propre transaction. La Persistence Foundation devra donc introduire une coordination additive compatible avec une transaction unique, sans modifier le repository historique ni sa migration.
