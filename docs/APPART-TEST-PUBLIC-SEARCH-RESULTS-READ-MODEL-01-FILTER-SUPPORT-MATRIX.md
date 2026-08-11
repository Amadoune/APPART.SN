# APPART.TEST Public Search Results Read Model 01 — Filter Support Matrix

| Filtre | Source claire | Support V1 | Justification |
|---|---|---|---|
| transaction | Non | NOT_SUPPORTED | Champ absent de la projection publique |
| localisation | Non | NOT_SUPPORTED | Aucun libellé public qualifié |
| type de bien | Oui dans le payload | NOT_SUPPORTED | Payload PHP sérialisé ; filtrage correct avant pagination non disponible sans nouvelle matérialisation/index |
| budget | Non | NOT_SUPPORTED | Prix public absent |

V1 est une lecture paginée de la collection publique, pas un nouveau moteur Search. Aucun filtre n'est simulé ou appliqué après pagination.
