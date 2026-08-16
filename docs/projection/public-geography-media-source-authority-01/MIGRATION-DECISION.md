# Migration decision

**NO MIGRATION** à ce stade. Les tables, PK, versions, causalités, payloads, checksums, contraintes et timestamps existent pour les deux sources.

Une migration future ne serait recevable que si un prochain gate démontre un état durable réellement absent; elle ne doit pas être créée pour faciliter l'orchestration.
