# Version Model

Le store impose une version positive et le writer définit : version inférieure → `RejectedObsolete`, même version/même checksum → `AlreadyApplied`, même version/checksum différent → `Divergent`, version supérieure → `Applied`.

Ce qui manque est la règle de production : version initiale, domination des trois révisions, évolution et cohérence de replay. Les commandes locales utilisent toujours version 1 sans constituer une autorité.

Cette règle doit être décidée avant implémentation.
