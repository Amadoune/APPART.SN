# Media Item Lifecycle PostgreSQL Specification

Table propriétaire : `media.media_item_lifecycle_transitions`.

Colonnes : `media_id`, `version`, `previous_state`, `current_state`, `action`, `transition_checksum`.

La version initiale est exactement 1 avec `Active`. PostgreSQL n'accepte ensuite que `Active / Remove / Removed` ou `Active / Archive / Archived`. La clé `(media_id, version)`, le verrou advisory et la transaction garantissent continuité, concurrence et unicité.
