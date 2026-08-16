# PostgreSQL Evidence

La campagne isolée `PostgreSqlPublicSearchDecisionMaterializationTest` est PASS : 1 test, 12 assertions.

Elle démontre :

- assemblage réel des sources owner ;
- écriture par le writer certifié ;
- UUIDv5 attendu ;
- version 1, rank 0 et facets vides ;
- replay `AlreadyApplied` sans doublon ;
- révision Media dominante matérialisée en version 2 avec identité stable.

La base de tests dédiée est distincte de la base applicative locale.
