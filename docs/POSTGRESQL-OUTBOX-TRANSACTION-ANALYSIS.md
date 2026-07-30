# PostgreSQL Outbox — analyse transactionnelle

## Audit des quatre repositories

Listing, Property, MediaCollection et AdministrativeAction reçoivent chacun une abstraction `*Transaction` injectable. Leur implémentation PostgreSQL par défaut ouvre et termine elle-même une transaction sur le PDO fourni. Injecter ces implémentations sous une enveloppe externe provoquerait une transaction imbriquée.

Les repositories n'ont toutefois aucune dépendance à leur implémentation transactionnelle concrète. `PostgreSqlAggregateOutboxParticipantTransaction` implémente les quatre contrats existants et exige qu'une transaction soit déjà active. Il exécute la closure du repository sans begin/commit. `PostgreSqlAggregateOutboxTransaction` possède alors l'unique begin, commit ou rollback.

Le repository, le Writer Outbox et l'enveloppe reçoivent exactement la même instance PDO. Le Writer détecte une transaction active et n'en ouvre aucune. Hors enveloppe, son append reste atomique localement pour ses deux tables.

## Réponses explicites

1. **Aggregate + Outbox dans une transaction : oui**, via l'enveloppe externe, le participant injecté et le Writer sur le même PDO.
2. **Connexion unique : oui**, par injection de la même instance ; les tests vérifient la visibilité depuis une connexion indépendante après commit.
3. **Risque d'imbrication : neutralisé**, le participant refuse de fonctionner sans transaction externe et l'enveloppe refuse toute transaction déjà ouverte.
4. **Rollback commun : oui**, toute exception après l'une des écritures rollback l'ensemble.
5. **Enveloppe externe suffisante : oui**, aucun repository ni contrat métier n'est modifié.

## Limite de certification actuelle

Le serveur PostgreSQL 18 local répond, mais les identifiants `appart_test` disponibles dans `.env.postgresql.example` n'incluent aucun mot de passe et l'authentification refuse la connexion. Les tests réels ont été créés mais leur exécution reste bloquée jusqu'à fourniture de `APPART_TEST_PG_PASSWORD`. La certification ne peut donc pas conclure GO dans cet état.
