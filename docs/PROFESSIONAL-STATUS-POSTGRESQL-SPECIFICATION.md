# Professional Status PostgreSQL Specification

## Journal

Table : `professionals.professional_status_transitions`.

Champs : `professional_id`, `version`, `previous_state`, `current_state`, `action`, `transition_checksum`.

La clé primaire `(professional_id, version)` interdit les doublons. Une contrainte de forme distingue l'initialisation des transitions. Une contrainte fermée protège les deux seules transitions certifiées.

L'index `(professional_id, version DESC)` couvre la lecture courante. Un verrou advisory transactionnel dérivé du `professional_id` sérialise les écritures concurrentes sans table de verrou supplémentaire.
