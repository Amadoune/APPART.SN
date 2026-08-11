# External CI — Evidence Collection Procedure

Pour un run externe futur, conserver :

- URL permanente du repository, du workflow et du run ;
- run ID, run attempt, event, ref, tag et `GITHUB_SHA` ;
- conclusion terminale de chaque step ;
- versions Runtime observées ;
- logs complets horodatés ;
- artefact de release téléchargé avant expiration ;
- manifeste, archive SHA-256 et tree SHA-256 ;
- attestation de propreté et d'identité ;
- durée, exit code et éventuels retries.

Les preuves doivent être exportées dans un emplacement durable et indexées sans secret. Une simple capture, la présence du YAML ou un résultat local ne suffisent pas.
