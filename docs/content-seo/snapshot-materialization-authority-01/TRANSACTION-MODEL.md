# Transaction Model

Chaque owner conserve sa transaction locale : Lifecycle publie, Search matérialise, ContentSeo relit puis écrit son snapshot, Projection consomme ensuite.

Le writer ContentSeo possède sa transaction et ses verrous. Les lectures owner constituent un ensemble révisionné ; une variation concurrente entraîne obsolete/divergent ou un nouveau snapshot, jamais une transaction distribuée.
