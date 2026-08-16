# Transaction Model

La persistance doit rester owner-locale SearchDiscovery. La table actuelle `public_search_decisions` suffit au writer spécialisé et supporte transaction externe/savepoint.

La transaction ne doit inclure ni Aggregates sources ni Projection Store. Les faits doivent être relus comme snapshots versionnés, puis la décision écrite localement.

La nécessité d'un journal owner-local ou d'une outbox Search ne peut pas être décidée après le fail-fast : les Foundations Search plus récentes décrivent un journal séparé, mais il n'est ni le store lu par Projection ni déployé dans la base applicative observée. Un futur chantier devra choisir explicitement sa relation avec `public_search_decisions`.

## Completion 01

La transaction finale contient uniquement le write `public_search_decisions` et le verrou owner-local déjà fournis par le Writer. Aucun journal ou outbox Search supplémentaire n'est requis pour v1 ; le handoff Listing est consommé hors de cette transaction.
