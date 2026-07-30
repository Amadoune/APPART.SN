# Lead Lifecycle Contextual Persistence Foundation

La migration 024 ajoute une table compagnon corrélée au journal 022 par `(lead_id, version)`. Elle ne crée volontairement aucune clé étrangère vers 022 afin de préserver le rollback historique autonome. Le repository contextuel garantit leur intégrité dans une transaction unique.

Le checksum contextuel couvre `leadId`, `version`, `actorId` et `occurredAt`. Un rejeu identique est idempotent; toute divergence de contexte est observable. Une transaction externe reste propriétaire du commit et permet un rollback intégral.

La génération 4.4B demeure disponible via `LeadLifecycleWorkflowStore`. Aucun binding Runtime n'est ajouté en 4.4D-R2.
