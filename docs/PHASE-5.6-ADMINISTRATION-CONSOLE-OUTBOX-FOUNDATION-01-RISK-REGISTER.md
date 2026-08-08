# Administration Console Outbox Foundation — Risk Register

| ID | Risque | Maîtrise |
|---|---|---|
| R1 | Source autre qu'une Delivery V1 | Union fermée dans Writer, Policy, Result et Repository |
| R2 | Identité instable | JSON canonique ordonné et SHA-256 |
| R3 | Collision avec contenu divergent | Checksum distinct et `DivergentMessage` |
| R4 | Double append concurrent | Clé primaire et `ON CONFLICT DO NOTHING` |
| R5 | Lecture non déterministe | Ordre `created_at, message_id` |
| R6 | Retry non borné | Contrainte SQL et filtre strict à 10 |
| R7 | Rupture d'une transaction externe | Savepoint local, jamais de commit externe |
| R8 | Date reconstruite hors UTC | Normalisation UTC microseconde |
| R9 | Type/statut incohérent | Contraintes SQL fermées et reconstruction typée sans fallback |
| R10 | Couplage Transport/Routing/Consumer | Aucun composant ni dépendance associé |
| R11 | Altération de migration 082 | Contrôle des deux empreintes SHA-256 |
| R12 | Rollback 083 destructif hors périmètre | Rollback limité à la table Outbox 083 |
