# Architecture Failure Classification

| Failure | Artefact réel | État baseline R1 | État normatif attendu | Cause | Correction minimale |
|---|---|---|---|---|---|
| `database/migrations` absent | répertoire local vide | aucun objet Git | répertoire requis par gate historique | omission manifest Git | `.gitkeep` |
| accès DB ExperienceAcceptance Outbox | repository 091 certifié | présent | infrastructure PostgreSQL autorisée | préfixe Outbox absent | préfixe nominatif exact |
| SQL ExperienceAcceptance Outbox | même repository | présent | SQL confiné à cette Infrastructure | même omission | même préfixe nominatif exact |
| repository concret absent | classe certifiée | présente | entrée nominative attendue | liste non synchronisée | entrée exacte classe/chemin |
| rollback 091 hors slice | rollback gelé présent | présent | slice Outbox 091 autorisée | préfixe migration absent | préfixe exact `Outbox/Migrations/` |

Aucun défaut métier ou architectural du repository n'est observé. Les tests dédiés Outbox certifient propriétaire, dépendances Delivery, concurrence, retry, schéma et empreintes gelées.
