# Professional Status Contextual Migration and Rollback Guide

La migration additive 028 crée `professionals.professional_status_transition_contexts` avec une clé primaire `(professional_id, version)`, une version supérieure à 1, un acteur UUID, un instant `timestamptz` et un checksum SHA-256.

Le rollback 028 supprime uniquement cette table. Aucun lien bloquant n'est créé vers le journal 027 : les migrations 027 et 028 peuvent être annulées indépendamment dans l'ordre exigé par l'exploitation.
