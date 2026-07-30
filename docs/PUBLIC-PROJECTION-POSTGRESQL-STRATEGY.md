# Public Projection PostgreSQL Strategy

Le schéma `public_projection` est séparé des Outbox modulaires. Les migrations utilisent `IF NOT EXISTS`, des contraintes CHECK, FK et index partiels. PostgreSQL 18 réel certifie le contrat.

La concurrence est arbitrée par verrou de la ligne génération puis contraintes uniques. Les transactions sont Read Committed par défaut ; aucune logique métier n'est déplacée dans SQL. Les payloads restent internes au Store et sont accompagnés d'un checksum technique.

La purge est interdite dans ce sprint. Les historiques, tombstones, candidats et générations ne sont supprimés par aucun chemin automatique.
